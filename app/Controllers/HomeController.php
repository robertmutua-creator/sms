<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Migration;

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
            $this->view('auth/login', ['title' => 'SMS - Login', 'moduleTitle' => 'Authentication']);
        }
    }
}
