<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Classes;
use App\Models\Schools;
use App\Models\Subjects;
use App\Models\Terms;
use App\Models\Users;
use App\Models\Years;

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
                $currentTerms = (new Terms($this->schoolId()))->findAll(['status' => 'current']);
                $currentTerm = $currentTerms[0] ?? null;
                $currentYear = $currentTerm === null
                    ? null
                    : (new Years($this->schoolId()))->find((int) $currentTerm['year_id']);
                $data['currentTermYear'] = $currentTerm !== null && $currentYear !== null
                    ? 'Term ' . $currentTerm['term'] . ' / ' . $currentYear['year']
                    : null;
                $this->view('admins/index', $data);
                break;
            case 'Teacher':
                echo "Welcome Teacher";
                echo '<form method="post" action="/logout"><input type="hidden" name="token" value="' . htmlspecialchars($this->setToken(), ENT_QUOTES, 'UTF-8') . '"><button type="submit">Logout</button></form>';
                break;
            case 'Student':
                echo "Welcome Student";
                echo '<form method="post" action="/logout"><input type="hidden" name="token" value="' . htmlspecialchars($this->setToken(), ENT_QUOTES, 'UTF-8') . '"><button type="submit">Logout</button></form>';
                break;

            default:
                $this->flash("error", "Unknown role");
                $this->redirect('/login');
                break;
        }
    }
}
