<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Subjects;

class SubjectsController extends Controller
{
    private Subjects $subjects;
    private string $basePath = '/admin/subjects';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized Access');
            $this->redirect('/dashboard');
        }

        $this->subjects = new Subjects($this->schoolId());
    }

    public function index(?string $id = null): void
    {
        $subject = [];
        if ($id !== null) {
            $subjectId = filter_var($id, FILTER_VALIDATE_INT);
            if ($subjectId === false || $subjectId < 1) {
                $this->flash('error', 'Invalid subject ID.');
                $this->redirect($this->basePath);
            }

            $subject = $this->subjects->find($subjectId);
            if ($subject === null) {
                $this->flash('error', 'Subject not found.');
                $this->redirect($this->basePath);
            }
        }

        $this->view('admins/subjects', [
            'subject' => $subject,
            'subjects' => $this->subjects->findAll(),
            'token' => $this->setToken(),
            'moduleTitle' => $subject ? 'Edit Subject: ' . $subject['name'] : 'Manage Subjects',
        ]);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $subjectId = $id === null ? null : filter_var($id, FILTER_VALIDATE_INT);
        if ($subjectId === false || ($subjectId !== null && $subjectId < 1)) {
            $this->flash('error', 'Invalid subject ID.');
            $this->redirect($this->basePath);
        }

        if ($subjectId !== null && $this->subjects->find($subjectId) === null) {
            $this->flash('error', 'Subject not found.');
            $this->redirect($this->basePath);
        }

        $codeInput = $_POST['code'] ?? '';
        $nameInput = $_POST['name'] ?? '';
        if (!is_string($codeInput) || !is_string($nameInput)) {
            $this->flash('error', 'Subject code and name must be valid text.');
            $this->redirect($this->basePath);
        }

        $code = trim($codeInput);
        $name = trim($nameInput);
        if ($code === '' || $name === '') {
            $this->flash('error', 'Subject code and name are required.');
            $this->redirect($this->basePath);
        }

        $subjectData = [
            'code' => $this->textFormat($code, 'upper'),
            'name' => $this->textFormat($name, 'title'),
        ];

        $saved = $subjectId !== null
            ? $this->subjects->update($subjectId, $subjectData)
            : $this->subjects->create($subjectData);
        if (!$saved) {
            $this->flash('error', 'Unable to save the subject. Check that the code is not already in use.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', $subjectId !== null ? 'Subject updated successfully.' : 'Subject created successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $subjectId = filter_var($id, FILTER_VALIDATE_INT);
        if ($subjectId === false || $subjectId < 1) {
            $this->flash('error', 'Invalid subject ID.');
            $this->redirect($this->basePath);
        }

        if ($this->subjects->find($subjectId) === null) {
            $this->flash('error', 'Subject not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->subjects->delete($subjectId)) {
            $this->flash('error', 'Unable to delete the subject.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', 'Subject deleted successfully.');
        $this->redirect($this->basePath);
    }
}
