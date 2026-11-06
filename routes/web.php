<?php

use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\SuperAdmin\AdminsController;
use App\Controllers\SuperAdmin\SchoolsController;
use App\Core\Router;

Router::get('/setup', [HomeController::class, 'setup']);

Router::get('/', [HomeController::class, 'home']);
Router::get('/login', [HomeController::class, 'login']);
Router::post('/login', [HomeController::class, 'login']);
Router::get('/logout', [HomeController::class, 'logout']);

Router::get('/dashboard', [DashboardController::class, 'index']);

Router::get('/superadmin/admins', [AdminsController::class, 'index']);
Router::get('/superadmin/admins/edit/{id}', [AdminsController::class, 'index']);
Router::post('/superadmin/admins/save', [AdminsController::class, 'save']);
Router::post('/superadmin/admins/update/{id}', [AdminsController::class, 'save']);
Router::post('/superadmin/admins/delete/{id}', [AdminsController::class, 'destroy']);

Router::get('/superadmin/schools', [SchoolsController::class, 'index']);
Router::get('/superadmin/schools/edit/{id}', [SchoolsController::class, 'index']);
Router::post('/superadmin/schools/save', [SchoolsController::class, 'save']);
Router::post('/superadmin/schools/update/{id}', [SchoolsController::class, 'update']);
Router::post('/superadmin/schools/delete/{id}', [SchoolsController::class, 'destroy']);
