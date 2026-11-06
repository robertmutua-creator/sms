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
                'token' => $this->setToken(),
                'moduleTitle' => 'Authentication'
            ];
            $this->view('auth/login', $data);
        } else {

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

    public function logout(): void
    {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie if it exists
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: /login");
        exit;
    }

    public function setup(): void
    {
        try {
            (new Migration());
            // superadmin pass 123
        } catch (\Throwable $th) {
            error_log('Error running migrations.' . $th->getMessage());
        }
    }
}
