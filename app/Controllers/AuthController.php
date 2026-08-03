<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;
use App\Libraries\NotificationService;
use App\Models\AdvocateDbModel;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login', [
            'title'      => 'Login',
            'editWindow' => \App\Models\ApplicationModel::editWindowInfo(),
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

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($email);

        if (! $user || ! $userModel->verifyPassword($user, $password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if (! $user['is_active'] && $user['is_active'] !== 't' && $user['is_active'] !== true && $user['is_active'] !== '1') {
            return redirect()->back()->with('error', 'Your account is inactive. Contact the Registry.');
        }

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        session()->set([
            'user_id'          => $user['id'],
            'name'             => $user['name'],
            'email'            => $user['email'],
            'mobile'           => $user['mobile'] ?? '',
            'enrolment_number' => $user['enrolment_number'] ?? '',
            'role'             => $user['role'],
            'isLoggedIn'       => true,
        ]);

        model(AuditLogModel::class)->log('login', (int) $user['id']);

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
     * Search advocate master data by enrolment number (JSON or form post).
     */
    public function lookupAdvocate()
    {
        $enrolment = AdvocateDbModel::normaliseEnrolment(
            (string) ($this->request->getPost('enrolment_number') ?? $this->request->getGet('enrolment_number') ?? '')
        );

        $wantsJson = $this->request->isAJAX()
            || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')
            || $this->request->getGet('format') === 'json';

        if ($enrolment === '') {
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

        $prefill = $model->toRegistrationPrefill($row);

        // Block if enrolment already registered
        $existing = model(UserModel::class)->findByEnrolment($enrolment);
        if ($existing) {
            $msg = 'An account already exists for this enrolment number. Please log in or use Forgot Password.';
            if ($wantsJson) {
                return $this->response->setJSON([
                    'found'   => true,
                    'already_registered' => true,
                    'message' => $msg,
                ] + $prefill);
            }

            return redirect()->to('/login')->with('error', $msg);
        }

        if ($wantsJson) {
            return $this->response->setJSON($prefill + [
                'already_registered' => false,
                'message'            => 'Advocate details found. Form fields have been filled.',
            ]);
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

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $enrolment = AdvocateDbModel::normaliseEnrolment((string) $this->request->getPost('enrolment_number'));
        $userModel = model(UserModel::class);

        if ($userModel->findByEnrolment($enrolment)) {
            return redirect()->back()->withInput()
                ->with('error', 'An account already exists for this enrolment number. Please log in.');
        }

        $payload = [
            'name'             => trim((string) $this->request->getPost('name')),
            'email'            => strtolower(trim((string) $this->request->getPost('email'))),
            'mobile'           => trim((string) $this->request->getPost('mobile')),
            'enrolment_number' => $enrolment,
            'password_hash'    => $userModel->hashPassword((string) $this->request->getPost('password')),
            'role'             => 'applicant',
            'is_active'        => true,
        ];
        $id = $userModel->insert($payload);

        model(AuditLogModel::class)->log('register', (int) $id, null, ['enrolment_number' => $enrolment]);

        (new NotificationService())->registration([
            'id'     => (int) $id,
            'name'   => $payload['name'],
            'email'  => $payload['email'],
            'mobile' => $payload['mobile'],
        ]);

        return redirect()->to('/login')->with('success', 'Registration successful. Please log in to continue.');
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

    private function verifyCaptcha(): bool
    {
        $captcha = new CaptchaService();

        return $captcha->verify($this->request->getPost('captcha'));
    }
}
