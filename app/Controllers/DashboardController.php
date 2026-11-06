<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $user = $_SESSION['user'];

        switch ($user['role']) {
            case 'SuperAdmin':
                echo "Welcome Super Admin.";
                echo "<a href='/logout'>Logout</a>";
                break;
            case 'SchoolAdmin':
                echo "Welcome School Admin";
                echo "<a href='/logout'>Logout</a>";
                break;
            case 'Teacher':
                echo "Welcome Teacher";
                echo "<a href='/logout'>Logout</a>";
                break;
            case 'Student':
                echo "Welcome Student";
                echo "<a href='/logout'>Logout</a>";
                break;

            default:
                $this->flash("error", "Unknown role");
                $this->redirect('/login');
                break;
        }
    }
}
