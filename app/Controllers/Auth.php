<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * Auth Controller — handles login, logout, and registration.
 */
class Auth extends BaseController
{
    protected UserModel $userModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    // ----------------------------------------------------------------
    // Login
    // ----------------------------------------------------------------

    /**
     * Show the login form.
     */
    public function login(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        // Already logged in → go to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }

        return view('auth/login', [
            'title'      => 'Login — Task Manager',
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Process login form submission.
     */
    public function doLogin(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->findByEmail($email);

        if (! $user || ! $this->userModel->verifyPassword($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        // Store user data in session
        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_email'=> $user['email'],
            'logged_in' => true,
        ]);

        return redirect()->to('/')->with('success', 'Selamat datang, ' . $user['name'] . '!');
    }

    // ----------------------------------------------------------------
    // Logout
    // ----------------------------------------------------------------

    /**
     * Destroy session and redirect to login.
     */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Berhasil logout.');
    }

    // ----------------------------------------------------------------
    // Register
    // ----------------------------------------------------------------

    /**
     * Show the registration form.
     */
    public function register(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }

        return view('auth/register', [
            'title'      => 'Daftar — Task Manager',
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Process registration form submission.
     */
    public function doRegister(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'email'    => ['is_unique' => 'Email sudah digunakan.'],
            'password_confirm' => ['matches' => 'Konfirmasi password tidak cocok.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->userModel->insert([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ]);

        if (! $userId) {
            return redirect()->back()->withInput()->with('error', 'Gagal mendaftar. Silakan coba lagi.');
        }

        return redirect()->to('/login')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }
}
