<?php

defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('User_model');
        $this->call->library('api');
    }

    public function register()
    {
        $this->api->require_method('POST');
        $data = $this->api->body();

        $username = $data['username'] ?? '';
        $email    = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->api->respond_error('Username, email, and password are required.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->api->respond_error('Invalid email address.', 422);
        }

        if (strlen($password) < 8) {
            $this->api->respond_error('Password must be at least 8 characters.', 422);
        }

        if ($this->User_model->find_by_email($email)) {
            $this->api->respond_error('Email is already registered.', 409);
        }

        if ($this->User_model->find_by_username($username)) {
            $this->api->respond_error('Username is already taken.', 409);
        }

        $id = $this->User_model->create_user([
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $this->api->respond([
            'message' => 'Registration successful. Please log in.',
            'user'    => ['id' => $id, 'username' => $username, 'email' => $email],
        ], 201);
    }

    public function login()
    {
        $this->api->require_method('POST');
        $data = $this->api->body();

        $email    = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->api->respond_error('Email and password are required.', 422);
        }

        $user = $this->User_model->find_by_email($email);

        if (!$user || !password_verify($password, $user['password']) || (int) $user['is_active'] !== 1) {
            $this->api->respond_error('Invalid email or password.', 401);
        }

        $scopes = $user['role'] === 'admin' ? ['read', 'write', 'delete'] : ['read'];

        $tokens = $this->api->issue_tokens([
            'id'     => $user['id'],
            'role'   => $user['role'],
            'scopes' => $scopes,
        ]);

        $this->api->respond([
            'message' => 'Login successful.',
            'user'    => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ],
            'tokens'  => $tokens,
        ]);
    }

    public function refresh()
    {
        $this->api->require_method('POST');
        $data = $this->api->body();
        $this->api->refresh_access_token($data['refresh_token'] ?? '');
    }

    public function logout()
    {
        $this->api->require_method('POST');
        $this->api->require_jwt();
        $data = $this->api->body();

        if (!empty($data['refresh_token'])) {
            $this->api->revoke_refresh_token($data['refresh_token']);
        }

        $this->api->respond(['message' => 'Logged out successfully.']);
    }
}