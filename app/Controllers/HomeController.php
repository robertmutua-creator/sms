<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Migration;
use App\Models\Users;

class HomeController extends Controller
{
    public function migrations(): void
    {
        (new Migration());
    }
    public function home(): void
    {
        require_once __DIR__ . '/../Views/home.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = [
                'title' => 'SMS - Login',
                'token' => $this->setToken()
            ];
            $this->view('/auth/login', $data);
        }

        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $token = $_POST['token'] ?? null;

        if (!$this->confirmToken($token)) {
            $this->flash('error', 'Invalid token received!');
            $this->redirect('/login');
        }

        if (empty($username) || empty($password)) {
            $this->flash('error', 'Both username and password are required.');
            $this->redirect('/login');
        }

        $user = (new Users())->findByUsername($username);

        if (empty($user) || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Incorrect credentials');
            $this->redirect('/login');
        }

        unset($user['password']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $this->redirect('/dashboard');
    }
}
