<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Classes;
use App\Models\Schools;
use App\Models\Subjects;
use App\Models\Users;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isLoggedIn()) {
            $this->flash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $user = $_SESSION['user'];
        $data['moduleTitle'] = 'Dashboard';
        switch ($user['role']) {
            case 'SuperAdmin':
                $data['usersCount'] = (new Users())->count();
                $data['schoolsCount'] = (new Schools())->count();
                $this->view('superadmin/index', $data);
                break;
            case 'SchoolAdmin':
                $data['studentsCount'] = (new Users($this->schoolId()))->count(['role' => 'Student']);
                $data['teachersCount'] = (new Users($this->schoolId()))->count(['role' => 'Teacher']);
                $data['classesCount'] = (new Classes($this->schoolId()))->count();
                $data['subjectsCount'] = (new Subjects($this->schoolId()))->count();
                $this->view('admins/index', $data);
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
