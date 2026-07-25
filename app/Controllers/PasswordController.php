<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;
use App\Libraries\PasswordMailer;
use App\Models\AuditLogModel;
use App\Models\PasswordResetModel;
use App\Models\UserModel;

class PasswordController extends BaseController
{
    // ─── Forgot password (guest) ───────────────────────────────────────

    public function forgot()
    {
        return view('auth/forgot_password', ['title' => 'Forgot password']);
    }

    public function sendResetLink()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'captcha' => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! (new CaptchaService())->verify($this->request->getPost('captcha'))) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user  = model(UserModel::class)->findByEmail($email);

        // Always show the same message (do not reveal whether the email exists)
        $generic = 'If an account exists for that email address, a password reset link has been sent. Please check your inbox (and spam folder). The link expires in 1 hour.';

        if ($user) {
            $plain = model(PasswordResetModel::class)->createToken($email);
            $url   = base_url('reset-password/' . $plain);

            (new PasswordMailer())->sendPasswordReset($email, $user['name'], $url, (int) $user['id']);

            model(AuditLogModel::class)->log('password_reset_requested', (int) $user['id'], null, [
                'email' => $email,
            ]);
        } else {
            model(AuditLogModel::class)->log('password_reset_unknown_email', null, null, [
                'email' => $email,
            ]);
        }

        return redirect()->to('/login')->with('success', $generic);
    }

    // ─── Reset password via token (guest) ──────────────────────────────

    public function reset(string $token = '')
    {
        $token = trim($token);
        $row   = $token !== '' ? model(PasswordResetModel::class)->findValidByToken($token) : null;

        if (! $row) {
            return redirect()->to('/forgot-password')
                ->with('error', 'This password reset link is invalid or has expired. Please request a new one.');
        }

        return view('auth/reset_password', [
            'title' => 'Reset password',
            'token' => $token,
            'email' => $row['email'],
        ]);
    }

    public function processReset(string $token = '')
    {
        $token = trim($token);
        $row   = $token !== '' ? model(PasswordResetModel::class)->findValidByToken($token) : null;

        if (! $row) {
            return redirect()->to('/forgot-password')
                ->with('error', 'This password reset link is invalid or has expired. Please request a new one.');
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'captcha'          => 'required|min_length[4]|max_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! (new CaptchaService())->verify($this->request->getPost('captcha'))) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired CAPTCHA. Please try again.');
        }

        $userModel = model(UserModel::class);
        $user      = $userModel->findByEmail($row['email']);

        if (! $user) {
            return redirect()->to('/forgot-password')->with('error', 'Account not found.');
        }

        $userModel->update($user['id'], [
            'password_hash' => $userModel->hashPassword((string) $this->request->getPost('password')),
        ]);

        model(PasswordResetModel::class)->markUsed((int) $row['id']);

        (new PasswordMailer())->sendPasswordChanged($user['email'], $user['name'], (int) $user['id']);

        model(AuditLogModel::class)->log('password_reset_completed', (int) $user['id']);

        return redirect()->to('/login')->with('success', 'Your password has been reset. Please sign in with your new password.');
    }

    // ─── Change password (authenticated) ───────────────────────────────

    public function change()
    {
        return view('auth/change_password', [
            'title' => 'Change password',
        ]);
    }

    public function processChange()
    {
        $userId = (int) session()->get('user_id');
        if (! $userId) {
            return redirect()->to('/login');
        }

        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = model(UserModel::class);
        $user      = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/login')->with('error', 'Session expired. Please sign in again.');
        }

        if (! $userModel->verifyPassword($user, (string) $this->request->getPost('current_password'))) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        $newPassword = (string) $this->request->getPost('password');
        if ($userModel->verifyPassword($user, $newPassword)) {
            return redirect()->back()->with('error', 'New password must be different from the current password.');
        }

        $userModel->update($userId, [
            'password_hash' => $userModel->hashPassword($newPassword),
        ]);

        (new PasswordMailer())->sendPasswordChanged($user['email'], $user['name'], $userId);

        model(AuditLogModel::class)->log('password_changed', $userId);

        return redirect()->back()->with('success', 'Your password has been changed successfully.');
    }
}
