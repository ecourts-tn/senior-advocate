<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;
use App\Libraries\LoginSecurityService;
use App\Libraries\LookupRateLimiter;
use App\Libraries\NotificationService;
use App\Models\AdvocateDbModel;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        $portalNotifications = [];
        try {
            $portalNotifications = model(\App\Models\DesignationNotificationModel::class)->withDocuments(5);
        } catch (\Throwable $e) {
            $portalNotifications = [];
        }

        return view('auth/login', [
            'title'               => 'Login',
            'editWindow'          => \App\Models\ApplicationModel::editWindowInfo(),
            'portalNotifications' => $portalNotifications,
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
            'captcha'  => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $security = new LoginSecurityService();

        // Account lock (email unlock) or temporary IP lockout.
        if ($lockMsg = $security->lockoutMessage($email)) {
            $accountLocked = $security->isAccountLocked($email)
                || $lockMsg === LoginSecurityService::ACCOUNT_LOCKED_MESSAGE;
            $security->recordFailure(
                $email,
                $accountLocked ? 'account_locked' : 'rate_limited',
                $this->userIdForEmail($email)
            );
            if ($accountLocked) {
                $security->persistLock(null, $email);
            }

            $redirect = redirect()->back()->withInput()->with('error', $lockMsg);
            if ($accountLocked) {
                $redirect = $redirect
                    ->with('account_locked', true)
                    ->with('locked_email', $email);
            }

            return $redirect;
        }

        if (! $this->verifyCaptcha()) {
            $security->recordFailure($email, 'captcha');

            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($email);

        // Same public message for unknown email and wrong password (do not reveal which).
        if (! $user || ! $userModel->verifyPassword($user, $password)) {
            $security->recordFailure(
                $email,
                'invalid_credentials',
                $user ? (int) $user['id'] : null
            );

            if ($user && $security->countRecentFailuresByEmail($email) >= LoginSecurityService::MAX_PER_EMAIL) {
                $security->persistLock($user, $email);

                return redirect()->back()->withInput()
                    ->with('error', LoginSecurityService::ACCOUNT_LOCKED_MESSAGE)
                    ->with('account_locked', true)
                    ->with('locked_email', $email);
            }

            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if (! $user['is_active'] && $user['is_active'] !== 't' && $user['is_active'] !== true && $user['is_active'] !== '1') {
            $security->recordFailure($email, 'inactive', (int) $user['id']);

            return redirect()->back()->with('error', 'Your account is inactive. Contact the Registry.');
        }

        // Registered users must verify their email before they can sign in.
        if (! $userModel->isEmailVerified($user)) {
            $security->recordFailure($email, 'unverified', (int) $user['id']);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Your email address is not verified. Please check your inbox for the verification link, or request a new one.')
                ->with('unverified_email', $user['email']);
        }

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        // Mitigate session fixation before granting dashboard access.
        session()->regenerate(true);
        session()->set([
            'user_id'          => $user['id'],
            'name'             => $user['name'],
            'email'            => $user['email'],
            'mobile'           => $user['mobile'] ?? '',
            'enrolment_number' => $user['enrolment_number'] ?? '',
            'role'             => $user['role'],
            'isLoggedIn'       => true,
        ]);

        $security->recordSuccess((int) $user['id'], (string) $user['email'], (string) $user['role']);

        if (in_array($user['role'], ['admin', 'reviewer', 'approver'], true)) {
            return redirect()->to('/admin')->with('success', 'Welcome, ' . $user['name']);
        }

        return redirect()->to('/applicant/dashboard')->with('success', 'Welcome, ' . $user['name']);
    }

    public function register()
    {
        return view('auth/register', [
            'title'     => 'Advocate Registration',
            'prefill'   => session()->getFlashdata('advocate_prefill') ?? [],
            'lookupMsg' => session()->getFlashdata('lookup_msg') ?? null,
            'lookupOk'  => session()->getFlashdata('lookup_ok') ?? null,
        ]);
    }

    /**
     * Search advocate master data by enrolment number (POST only).
     *
     * Protections: CSRF (global), CAPTCHA, IP/session rate limiting.
     * Returns limited prefill fields for registration — never full advocate rows.
     */
    public function lookupAdvocate()
    {
        $wantsJson = $this->request->isAJAX()
            || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')
            || $this->request->getPost('format') === 'json'
            || $this->request->getGet('format') === 'json';

        // GET is disabled to reduce scraping of advocate data.
        if (strtolower($this->request->getMethod()) !== 'post') {
            if ($wantsJson) {
                return $this->response->setJSON([
                    'found'   => false,
                    'message' => 'Enrolment lookup must be submitted via POST with CAPTCHA.',
                ])->setStatusCode(405);
            }

            return redirect()->to('/register')->with('error', 'Please use the Search button on the registration form.');
        }

        $limiter = new LookupRateLimiter();
        $gate    = $limiter->check();
        if (! $gate['allowed']) {
            $limiter->record('rate_limited');

            if ($wantsJson) {
                return $this->response
                    ->setHeader('Retry-After', (string) ($gate['retry_after'] ?? LookupRateLimiter::WINDOW_SECONDS))
                    ->setJSON([
                        'found'   => false,
                        'message' => $gate['message'],
                    ])->setStatusCode(429);
            }

            return redirect()->to('/register')->with('error', $gate['message']);
        }

        // Search uses its own captcha scope (independent of registration submit captcha).
        $lookupCaptcha = (string) ($this->request->getPost('lookup_captcha')
            ?? $this->request->getPost('captcha')
            ?? '');
        $captchaOk = (new CaptchaService())->verify($lookupCaptcha, CaptchaService::SCOPE_LOOKUP);
        if (! $captchaOk) {
            $limiter->record('captcha_failed');

            if ($wantsJson) {
                return $this->response->setJSON([
                    'found'            => false,
                    'captcha_required' => true,
                    'message'          => 'Invalid or expired search CAPTCHA. Please complete the search security check and try again.',
                ])->setStatusCode(422);
            }

            return redirect()->to('/register')
                ->withInput()
                ->with('error', 'Invalid or expired search CAPTCHA. Please complete the search security check and try again.');
        }

        $enrolment = AdvocateDbModel::normaliseEnrolment(
            (string) ($this->request->getPost('enrolment_number') ?? '')
        );

        if ($enrolment === '') {
            $limiter->record('empty_enrolment');

            if ($wantsJson) {
                return $this->response->setJSON([
                    'found'   => false,
                    'message' => 'Please enter an enrolment number.',
                ])->setStatusCode(422);
            }

            return redirect()->to('/register')->with('error', 'Please enter an enrolment number to search.');
        }

        $model = model(AdvocateDbModel::class);
        $row   = $model->findByEnrolment($enrolment);

        if (! $row) {
            $limiter->record('not_found', $enrolment);
            $payload = [
                'found'            => false,
                'enrolment_number' => $enrolment,
                'message'          => 'No advocate found for this enrolment number. Please enter your details manually.',
            ];

            if ($wantsJson) {
                return $this->response->setJSON($payload);
            }

            return redirect()->to('/register')
                ->withInput()
                ->with('lookup_ok', false)
                ->with('lookup_msg', $payload['message'])
                ->with('advocate_prefill', ['enrolment_number' => $enrolment]);
        }

        // Block if enrolment already registered — do not return advocate PII.
        $existing = model(UserModel::class)->findByEnrolment($enrolment);
        if ($existing) {
            $limiter->record('already_registered', $enrolment, ['user_id' => (int) $existing['id']]);
            $key = AdvocateDbModel::parseNumberAndYear($enrolment);
            $msg = $key !== null
                ? 'An account already exists for enrolment number ' . $key['number'] . '/' . $key['year'] . '. Please log in or use Forgot Password.'
                : 'An account already exists for this enrolment number. Please log in or use Forgot Password.';
            if ($wantsJson) {
                return $this->response->setJSON([
                    'found'              => true,
                    'already_registered' => true,
                    'enrolment_number'   => $enrolment,
                    'message'            => $msg,
                ]);
            }

            return redirect()->to('/login')->with('error', $msg);
        }

        $prefill = $model->toRegistrationPrefill($row);
        // Limit JSON to registration-needed fields only (no raw DB dump).
        $safePrefill = [
            'found'            => true,
            'already_registered' => false,
            'enrolment_number' => $prefill['enrolment_number'] ?? $enrolment,
            'name'             => $prefill['name'] ?? '',
            'mobile'           => $prefill['mobile'] ?? '',
            'message'          => 'Advocate details found. Form fields have been filled.',
        ];

        $limiter->record('found', $enrolment);

        if ($wantsJson) {
            return $this->response->setJSON($safePrefill);
        }

        return redirect()->to('/register')
            ->with('lookup_ok', true)
            ->with('lookup_msg', 'Advocate details found. Please verify and complete the remaining fields.')
            ->with('advocate_prefill', $prefill);
    }

    public function attemptRegister()
    {
        $rules = [
            'enrolment_number' => 'required|min_length[3]|max_length[40]',
            'name'             => 'required|min_length[2]|max_length[255]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'mobile'           => 'required|min_length[10]|max_length[15]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'captcha'          => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Registration submit uses a separate captcha from enrolment search.
        if (! $this->verifyCaptcha(CaptchaService::SCOPE_REGISTER)) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please complete the registration security check and try again.');
        }

        $enrolment = AdvocateDbModel::normaliseEnrolment((string) $this->request->getPost('enrolment_number'));
        $userModel = model(UserModel::class);

        if ($userModel->findByEnrolment($enrolment)) {
            $key = AdvocateDbModel::parseNumberAndYear($enrolment);
            $msg = $key !== null
                ? 'An account already exists for enrolment number ' . $key['number'] . '/' . $key['year'] . '. Please log in.'
                : 'An account already exists for this enrolment number. Please log in.';

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $payload = [
            'name'             => trim((string) $this->request->getPost('name')),
            'email'            => strtolower(trim((string) $this->request->getPost('email'))),
            'mobile'           => trim((string) $this->request->getPost('mobile')),
            'enrolment_number' => $enrolment,
            'password_hash'    => $userModel->hashPassword((string) $this->request->getPost('password')),
            'role'             => 'applicant',
            'is_active'        => true,
            'email_verified_at'=> null,
        ];
        $id = $userModel->insert($payload);

        $plainToken = $userModel->issueEmailVerificationToken((int) $id);
        $verifyUrl  = base_url('verify-email/' . $plainToken);

        model(AuditLogModel::class)->log('register', (int) $id, null, ['enrolment_number' => $enrolment]);

        (new NotificationService())->emailVerification([
            'id'     => (int) $id,
            'name'   => $payload['name'],
            'email'  => $payload['email'],
            'mobile' => $payload['mobile'],
        ], $verifyUrl);

        return redirect()->to('/login')
            ->with('success', 'Registration successful. A verification link has been sent to your email. Please verify your email before logging in.')
            ->with('unverified_email', $payload['email']);
    }

    /**
     * Confirm email via one-time link from registration / resend email.
     */
    public function verifyEmail(string $token = '')
    {
        $result = model(UserModel::class)->consumeEmailVerificationToken($token);

        if (! empty($result['ok'])) {
            $user = $result['user'] ?? null;
            model(AuditLogModel::class)->log(
                ($result['reason'] ?? '') === 'already' ? 'email_already_verified' : 'email_verified',
                $user ? (int) $user['id'] : null
            );

            $msg = ($result['reason'] ?? '') === 'already'
                ? 'Your email is already verified. You may log in.'
                : 'Email verified successfully. You may now log in.';

            return redirect()->to('/login')->with('success', $msg);
        }

        $reason = $result['reason'] ?? 'invalid';
        if ($reason === 'expired') {
            return redirect()->to('/resend-verification')
                ->with('error', 'This verification link has expired. Please request a new verification email.')
                ->with('unverified_email', $result['user']['email'] ?? null);
        }

        return redirect()->to('/resend-verification')
            ->with('error', 'This verification link is invalid or has already been used. Please request a new verification email.');
    }

    public function resendVerification()
    {
        return view('auth/resend_verification', [
            'title' => 'Resend email verification',
            'email' => old('email') ?: (session()->getFlashdata('unverified_email') ?? ''),
        ]);
    }

    public function sendVerificationLink()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'captcha' => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $email     = strtolower(trim((string) $this->request->getPost('email')));
        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($email);

        // Same response whether or not the account exists (do not leak registration status).
        $generic = 'If an unverified account exists for that email address, a new verification link has been sent. Please check your inbox (and spam folder).';

        if ($user && ! $userModel->isEmailVerified($user)) {
            if (! $userModel->canResendEmailVerification($user)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please wait a minute before requesting another verification email.')
                    ->with('unverified_email', $email);
            }

            $plainToken = $userModel->issueEmailVerificationToken((int) $user['id']);
            $verifyUrl  = base_url('verify-email/' . $plainToken);

            (new NotificationService())->emailVerification([
                'id'     => (int) $user['id'],
                'name'   => $user['name'],
                'email'  => $user['email'],
                'mobile' => $user['mobile'] ?? '',
            ], $verifyUrl);

            model(AuditLogModel::class)->log('email_verification_resent', (int) $user['id'], null, [
                'email' => $email,
            ]);
        } else {
            model(AuditLogModel::class)->log('email_verification_resend_skipped', null, null, [
                'email'  => $email,
                'reason' => $user ? 'already_verified' : 'unknown_email',
            ]);
        }

        return redirect()->to('/login')->with('success', $generic);
    }

    /**
     * Unlock a locked account via the one-time link emailed after failed logins.
     */
    public function unlockAccount(string $token = '')
    {
        $result = model(UserModel::class)->consumeUnlockToken($token);

        if (! empty($result['ok'])) {
            $user = $result['user'] ?? null;
            model(AuditLogModel::class)->log(
                ($result['reason'] ?? '') === 'already' ? 'account_already_unlocked' : 'account_unlocked',
                $user ? (int) $user['id'] : null,
                null,
                ['email' => $user['email'] ?? null],
                'auth',
                $user ? (int) $user['id'] : null
            );

            $msg = ($result['reason'] ?? '') === 'already'
                ? 'Your account is already unlocked. You may log in.'
                : 'Your account has been unlocked. You may now log in.';

            return redirect()->to('/login')->with('success', $msg);
        }

        $reason = $result['reason'] ?? 'invalid';
        if ($reason === 'expired') {
            return redirect()->to('/request-unlock')
                ->with('error', 'This unlock link has expired. Submit the form below to receive a new unlock email.')
                ->with('locked_email', $result['user']['email'] ?? null);
        }

        return redirect()->to('/request-unlock')
            ->with('error', 'This unlock link is invalid or has already been used. Submit the form below if you still need to unlock your account.');
    }

    /**
     * Separate page: user confirms email and requests the unlock email.
     */
    public function requestUnlock()
    {
        $email = old('email')
            ?: (string) (session()->getFlashdata('locked_email') ?? '')
            ?: strtolower(trim((string) ($this->request->getGet('email') ?? '')));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        return view('auth/request_unlock', [
            'title' => 'Unlock account',
            'email' => $email,
        ]);
    }

    /**
     * Send the unlock email only after the user submits the unlock form.
     */
    public function sendUnlockLink()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'captcha' => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $security = new LoginSecurityService();
        $user     = model(UserModel::class)->findByEmail($email);

        $generic = 'If this account is locked, a one-time unlock link has been sent to the registered email. Please check your inbox and spam folder. The link expires in 1 hour.';

        if ($user && $security->isAccountLocked($email)) {
            if (! model(UserModel::class)->canSendUnlockEmail($user)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please wait a minute before requesting another unlock email.')
                    ->with('locked_email', $email);
            }

            $security->sendUnlockEmailIfAllowed((int) $user['id']);
        } else {
            model(AuditLogModel::class)->log('account_unlock_email_skipped', $user ? (int) $user['id'] : null, null, [
                'email'  => $email,
                'reason' => $user ? 'not_locked' : 'unknown_email',
            ], 'auth', $user ? (int) $user['id'] : null);
        }

        return redirect()->to('/login')->with('success', $generic);
    }

    public function logout()
    {
        $userId = session()->get('user_id');
        if ($userId) {
            model(AuditLogModel::class)->log('logout', (int) $userId);
        }
        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    /**
     * Verify the posted captcha for a given scope (default for login / other forms).
     */
    private function verifyCaptcha(?string $scope = null): bool
    {
        $captcha = new CaptchaService();

        return $captcha->verify($this->request->getPost('captcha'), $scope);
    }

    private function userIdForEmail(string $email): ?int
    {
        $user = model(UserModel::class)->findByEmail($email);

        return $user ? (int) $user['id'] : null;
    }
}
