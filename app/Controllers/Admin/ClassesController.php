<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Classes;

class ClassesController extends Controller
{
    private Classes $classes;
    private string $basePath = '/admin/classes';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized Access');
            $this->redirect('/dashboard');
        }

        $this->classes = new Classes($this->schoolId());
    }

    public function index(?string $id = null): void
    {
        $class = [];
        if ($id !== null) {
            $classId = filter_var($id, FILTER_VALIDATE_INT);
            if ($classId === false || $classId < 1) {
                $this->flash('error', 'Invalid class ID.');
                $this->redirect($this->basePath);
            }

            $class = $this->classes->find($classId);
            if ($class === null) {
                $this->flash('error', 'Class not found.');
                $this->redirect($this->basePath);
            }
        }

        $this->view('admins/classes', [
            'class' => $class,
            'classes' => $this->classes->findAll(),
            'token' => $this->setToken(),
            'moduleTitle' => $class ? 'Edit Class: ' . $class['name'] : 'Manage Classes',
        ]);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $classId = $id === null ? null : filter_var($id, FILTER_VALIDATE_INT);
        if ($classId === false || ($classId !== null && $classId < 1)) {
            $this->flash('error', 'Invalid class ID.');
            $this->redirect($this->basePath);
        }

        if ($classId !== null && $this->classes->find($classId) === null) {
            $this->flash('error', 'Class not found.');
            $this->redirect($this->basePath);
        }

        $codeInput = $_POST['code'] ?? '';
        $nameInput = $_POST['name'] ?? '';
        if (!is_string($codeInput) || !is_string($nameInput)) {
            $this->flash('error', 'Class code and name must be valid text.');
            $this->redirect($this->basePath);
        }

        $code = trim($codeInput);
        $name = trim($nameInput);
        if ($code === '' || $name === '') {
            $this->flash('error', 'Class code and name are required.');
            $this->redirect($this->basePath);
        }

        $classData = [
            'code' => $this->textFormat($code, 'upper'),
            'name' => $this->textFormat($name, 'title'),
        ];

        $saved = $classId !== null
            ? $this->classes->update($classId, $classData)
            : $this->classes->create($classData);
        if (!$saved) {
            $this->flash('error', 'Unable to save the class. Check that the code is not already in use.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', $classId !== null ? 'Class updated successfully.' : 'Class created successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $classId = filter_var($id, FILTER_VALIDATE_INT);
        if ($classId === false || $classId < 1) {
            $this->flash('error', 'Invalid class ID.');
            $this->redirect($this->basePath);
        }

        if ($this->classes->find($classId) === null) {
            $this->flash('error', 'Class not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->classes->delete($classId)) {
            $this->flash('error', 'Unable to delete the class.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', 'Class deleted successfully.');
        $this->redirect($this->basePath);
    }
}
