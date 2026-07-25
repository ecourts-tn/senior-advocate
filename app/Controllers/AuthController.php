<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;
use App\Libraries\NotificationService;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login', ['title' => 'Login']);
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
            'user_id'    => $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);

        model(AuditLogModel::class)->log('login', (int) $user['id']);

        if (in_array($user['role'], ['admin', 'reviewer'], true)) {
            return redirect()->to('/admin')->with('success', 'Welcome, ' . $user['name']);
        }

        return redirect()->to('/applicant/dashboard')->with('success', 'Welcome, ' . $user['name']);
    }

    public function register()
    {
        return view('auth/register', ['title' => 'Advocate Registration']);
    }

    public function attemptRegister()
    {
        $rules = [
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

        $userModel = model(UserModel::class);
        $payload   = [
            'name'          => trim((string) $this->request->getPost('name')),
            'email'         => strtolower(trim((string) $this->request->getPost('email'))),
            'mobile'        => trim((string) $this->request->getPost('mobile')),
            'password_hash' => $userModel->hashPassword((string) $this->request->getPost('password')),
            'role'          => 'applicant',
            'is_active'     => true,
        ];
        $id = $userModel->insert($payload);

        model(AuditLogModel::class)->log('register', (int) $id);

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
