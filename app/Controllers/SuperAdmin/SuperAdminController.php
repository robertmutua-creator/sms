<?php

namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Models\Schools;
use App\Models\Users;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SuperAdmin')) {
            $this->flash('error', 'Unauthorised Access!');
            $this->redirect('dashboard');
        }
    }

    public function index(): void
    {
        $data['pageTitle'] = 'System Dashboard';
        $data['moduleTitle'] = 'Dashboard';
        $data['usersCount'] = (new Users())->count();
        $data['schoolsCount'] = (new Schools())->count();
        $this->view('superadmin/index', $data);
    }
}
