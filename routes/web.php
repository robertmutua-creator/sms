<?php

use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\Admin\ClassesController;
use App\Controllers\Admin\SubjectsController;
use App\Controllers\SuperAdmin\AdminsController;
use App\Controllers\SuperAdmin\SchoolsController;
use App\Core\Router;

Router::get('/setup', [HomeController::class, 'setup']);

Router::get('/', [HomeController::class, 'home']);
Router::get('/login', [HomeController::class, 'login']);
Router::post('/login', [HomeController::class, 'login']);
Router::post('/logout', [HomeController::class, 'logout']);

Router::get('/dashboard', [DashboardController::class, 'index']);

Router::get('/admin/classes', [ClassesController::class, 'index']);
Router::get('/admin/classes/edit/{id}', [ClassesController::class, 'index']);
Router::post('/admin/classes/save', [ClassesController::class, 'save']);
Router::put('/admin/classes/update/{id}', [ClassesController::class, 'save']);
Router::patch('/admin/classes/update/{id}', [ClassesController::class, 'save']);
Router::delete('/admin/classes/delete/{id}', [ClassesController::class, 'destroy']);

Router::get('/admin/subjects', [SubjectsController::class, 'index']);
Router::get('/admin/subjects/edit/{id}', [SubjectsController::class, 'index']);
Router::post('/admin/subjects/save', [SubjectsController::class, 'save']);
Router::put('/admin/subjects/update/{id}', [SubjectsController::class, 'save']);
Router::patch('/admin/subjects/update/{id}', [SubjectsController::class, 'save']);
Router::delete('/admin/subjects/delete/{id}', [SubjectsController::class, 'destroy']);

Router::get('/superadmin/admins', [AdminsController::class, 'index']);
Router::get('/superadmin/admins/edit/{id}', [AdminsController::class, 'index']);
Router::post('/superadmin/admins/save', [AdminsController::class, 'save']);
Router::put('/superadmin/admins/update/{id}', [AdminsController::class, 'save']);
Router::patch('/superadmin/admins/update/{id}', [AdminsController::class, 'save']);
Router::delete('/superadmin/admins/delete/{id}', [AdminsController::class, 'destroy']);

Router::get('/superadmin/schools', [SchoolsController::class, 'index']);
Router::get('/superadmin/schools/edit/{id}', [SchoolsController::class, 'index']);
Router::post('/superadmin/schools/save', [SchoolsController::class, 'save']);
Router::put('/superadmin/schools/update/{id}', [SchoolsController::class, 'save']);
Router::patch('/superadmin/schools/update/{id}', [SchoolsController::class, 'save']);
Router::delete('/superadmin/schools/delete/{id}', [SchoolsController::class, 'destroy']);
