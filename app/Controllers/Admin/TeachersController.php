<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Users;
use Throwable;

class TeachersController extends Controller
{
    private Users $teachers;
    private string $basePath = '/admin/teachers';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized access');
            $this->redirect('/dashboard');
        }

        $this->teachers = new Users($this->schoolId());
    }

    public function index(?string $id = null): void
    {
        $teacher = [];
        if ($id !== null) {
            $teacherId = filter_var($id, FILTER_VALIDATE_INT);
            if ($teacherId === false || $teacherId < 1) {
                $this->flash('error', 'Invalid teacher ID.');
                $this->redirect($this->basePath);
            }

            $teacher = $this->teachers->find($teacherId);
            if ($teacher === null || ($teacher['role'] ?? null) !== 'Teacher') {
                $this->flash('error', 'Teacher not found.');
                $this->redirect($this->basePath);
            }
        }

        $this->view('admins/teachers', [
            'teacher' => $teacher,
            'teachers' => $this->teachers->findAll(['role' => 'Teacher']),
            'token' => $this->setToken(),
            'moduleTitle' => !empty($teacher) ? 'Edit Teacher: ' . htmlspecialchars($teacher['name'] ?? '') : 'Manage Teachers',
        ]);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $teacherId = null;
        $existingTeacher = null;
        if ($id !== null) {
            $teacherId = filter_var($id, FILTER_VALIDATE_INT);
            if ($teacherId === false || $teacherId < 1) {
                $this->flash('error', 'Invalid teacher ID.');
                $this->redirect($this->basePath);
            }

            $existingTeacher = $this->teachers->find($teacherId);
            if ($existingTeacher === null || ($existingTeacher['role'] ?? null) !== 'Teacher') {
                $this->flash('error', 'Teacher not found.');
                $this->redirect($this->basePath);
            }
        }

        $nameInput = $_POST['name'] ?? '';
        $tscInput = $_POST['tsc'] ?? '';
        $emailInput = $_POST['email'] ?? '';
        if (!is_string($nameInput) || !is_string($tscInput) || !is_string($emailInput)) {
            $this->flash('error', 'Teacher name, TSC number, and email must be valid text.');
            $this->redirect($this->basePath);
        }

        $name = $this->textFormat(trim($nameInput), 'title');
        $tscNumber = strtoupper(trim($tscInput));
        $email = strtolower(trim($emailInput));
        if ($name === '' || $tscNumber === '' || $email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->flash('error', 'Enter a name, TSC number, and valid email address.');
            $this->redirect($this->basePath);
        }

        $teacherData = [
            'name' => $name,
            'username' => $tscNumber,
            'email' => $email,
            'role' => 'Teacher',
        ];
        if ($teacherId === null) {
            $teacherData['password'] = password_hash($email, PASSWORD_DEFAULT);
        }

        $profile = $_FILES['profile'] ?? null;
        $profilePath = null;
        if ($profile !== null && ($profile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $profilePath = $this->handleFileUpload($profile, 'profiles');
            if ($profilePath !== null) {
                $teacherData['profile_photo'] = $profilePath;
            }
        }

        try {
            $saved = $teacherId !== null
                ? $this->teachers->update($teacherId, $teacherData)
                : $this->teachers->create($teacherData);

            if (!$saved) {
                if ($profilePath !== null) {
                    $this->deleteFile($profilePath);
                }
                $this->flash('error', 'Unable to save teacher information. Check that the TSC number and email are not already in use.');
                $this->redirect($this->basePath);
            }

            if ($existingTeacher && $profilePath !== null && !empty($existingTeacher['profile_photo'])) {
                $this->deleteFile($existingTeacher['profile_photo']);
            }

            $this->flash('success', $teacherId !== null ? 'Teacher updated successfully.' : 'Teacher saved successfully.');
        } catch (Throwable $th) {
            error_log('Failed to save/update teacher info: ' . $th->getMessage());
            if ($profilePath !== null) {
                $this->deleteFile($profilePath);
            }
            $this->flash('error', 'An error occurred while saving teacher information.');
        }

        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $teacherId = filter_var($id, FILTER_VALIDATE_INT);
        $teacher = $teacherId ? $this->teachers->find($teacherId) : null;
        if (!$teacher || ($teacher['role'] ?? null) !== 'Teacher') {
            $this->flash('error', 'Teacher not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->teachers->delete($teacherId)) {
            $this->flash('error', 'Unable to delete the teacher.');
            $this->redirect($this->basePath);
        }

        if (!empty($teacher['profile_photo'])) {
            $this->deleteFile($teacher['profile_photo']);
        }

        $this->flash('success', 'Teacher successfully deleted.');
        $this->redirect($this->basePath);
    }
}
