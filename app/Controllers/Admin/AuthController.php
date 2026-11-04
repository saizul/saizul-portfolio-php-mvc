<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\LoginAttempt;
use App\Models\PasswordReset;
use App\Models\User;

/**
 * Admin AuthController
 *
 * Routes:
 *   GET  /admin/login          → loginForm()
 *   POST /admin/login          → login()
 *   GET  /admin/logout         → logout()
 *   GET  /admin/forgot         → forgotForm()
 *   POST /admin/forgot         → forgot()
 *   GET  /admin/reset/{token}  → resetForm()
 *   POST /admin/reset/{token}  → reset()
 */
class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/admin'));
        }

        $this->render('admin.auth.login', [
            'flash'      => $this->getFlash(),
            'csrf_field' => Auth::csrfField(),
        ], 'admin-bare');
    }

    public function login(): void
    {
        $this->verifyCsrf();
        Auth::rotateCsrfToken();

        $email    = trim((string) $this->post('email', ''));
        $password = (string) $this->post('password', '');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect(url('/admin/login'));
        }

        $result = Auth::login($email, $password);

        if ($result === 'blocked') {
            $seconds = (new LoginAttempt())->secondsUntilUnblocked($email);
            $mins    = (int) ceil($seconds / 60);
            $this->flash('error', "Too many failed attempts. Try again in {$mins} minute(s).");
            $this->redirect(url('/admin/login'));
        }

        if ($result === 'invalid') {
            $this->flash('error', 'Invalid email or password.');
            $this->redirect(url('/admin/login'));
        }

        $intended = $_SESSION['intended_url'] ?? url('/admin');
        unset($_SESSION['intended_url']);
        $this->redirect($intended);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(url('/admin/login'));
    }

    public function forgotForm(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/admin'));
        }

        $this->render('admin.auth.forgot', [
            'flash'      => $this->getFlash(),
            'csrf_field' => Auth::csrfField(),
        ], 'admin-bare');
    }

    public function forgot(): void
    {
        $this->verifyCsrf();
        Auth::rotateCsrfToken();

        $email = strtolower(trim((string) $this->post('email', '')));

        if (!isValidEmail($email)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect(url('/admin/forgot'));
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if ($user && $user['is_active']) {
            $resetModel = new PasswordReset();
            $rawToken   = $resetModel->createToken($email);
            $resetUrl   = url('/admin/reset/' . $rawToken);
            (new Mailer())->sendPasswordReset($email, $user['name'], $resetUrl);
        }

        // Always same response — prevents user enumeration
        $this->flash(
            'success',
            'If an account exists for that email, a reset link has been sent. Check your inbox.'
        );
        $this->redirect(url('/admin/forgot'));
    }

    public function resetForm(string $token): void
    {
        $record = (new PasswordReset())->findValidToken($token);

        if (!$record) {
            $this->flash('error', 'This reset link is invalid or has expired.');
            $this->redirect(url('/admin/forgot'));
        }

        $this->render('admin.auth.reset', [
            'token'      => e($token),
            'flash'      => $this->getFlash(),
            'csrf_field' => Auth::csrfField(),
        ], 'admin-bare');
    }

    public function reset(string $token): void
    {
        $this->verifyCsrf();
        Auth::rotateCsrfToken();

        $resetModel = new PasswordReset();
        $record     = $resetModel->findValidToken($token);

        if (!$record) {
            $this->flash('error', 'This reset link is invalid or has expired.');
            $this->redirect(url('/admin/forgot'));
        }

        $password        = (string) $this->post('password', '');
        $passwordConfirm = (string) $this->post('password_confirm', '');

        $errors = [];
        if (strlen($password) < 8)                     $errors[] = 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password))          $errors[] = 'Must contain at least one uppercase letter.';
        if (!preg_match('/[0-9]/', $password))          $errors[] = 'Must contain at least one number.';
        if ($password !== $passwordConfirm)             $errors[] = 'Passwords do not match.';

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $this->redirect(url('/admin/reset/' . $token));
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($record['email']);

        if (!$user) {
            $this->flash('error', 'Account not found.');
            $this->redirect(url('/admin/forgot'));
        }

        $userModel->updatePassword((int) $user['id'], $password);
        $resetModel->consume((int) $record['id']);
        (new LoginAttempt())->clearFor($record['email']);

        $this->flash('success', 'Password updated. You can now log in.');
        $this->redirect(url('/admin/login'));
    }
}
