<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Schools;
use App\Models\Users;
use Throwable;

class StudentsController extends Controller
{
    private Users $students;
    private string $basePath;
    private Schools $school;

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized access');
            $this->redirect('/dashboard');
        }
        $this->students = new Users($this->schoolId());
        $this->basePath = '/admin/students';
        $this->school = new Schools();
    }

    public function index(?string $id = null): void
    {
        $student = [];
        if ($id !== null) {
            $studentId = filter_var($id, FILTER_VALIDATE_INT);
            if ($studentId === false || $studentId < 1) {
                $this->flash('error', 'Invalid student ID');
                $this->redirect($this->basePath);
            }

            $student = $this->students->find($studentId);

            if ($student === null || ($student['role'] ?? null) !== 'Student') {
                $this->flash('error', 'Student not found.');
                $this->redirect($this->basePath);
            }
        }

        $data['student'] = $student;
        $data['students'] = $this->students->findAll(['role' => 'Student']);
        $data['token'] = $this->setToken();
        $data['moduleTitle'] = !empty($student) ? 'Edit Student: ' . htmlspecialchars($student['name'] ?? '') : 'Manage Students';
        $this->view('admins/students', $data);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $studentId = null;
        $existingStudent = null;

        if ($id !== null) {
            $studentId = filter_var($id, FILTER_VALIDATE_INT);
            if ($studentId === false || $studentId < 1) {
                $this->flash('error', 'Invalid student ID');
                $this->redirect($this->basePath);
            }

            $existingStudent = $this->students->find($studentId);
            if ($existingStudent === null || ($existingStudent['role'] ?? null) !== 'Student') {
                $this->flash('error', 'Student not found.');
                $this->redirect($this->basePath);
            }
        }

        $fullName = $this->textFormat(trim($_POST['name'] ?? ''), 'title');
        $admNo = $this->textFormat(trim($_POST['adm'] ?? ''), 'upper');
        $upi = $this->textFormat(trim($_POST['upi'] ?? ''), 'upper');
        $status = $_POST['status'] ?? 'Active';
        $profile = $_FILES['profile'] ?? null;

        if ($fullName === '' || $admNo === '' || $upi === '') {
            $this->flash('error', 'All fields are required.');
            $this->redirect($this->basePath);
        }

        $school = $this->school->find($this->schoolId());
        $schoolUsername = $school['username'] ?? '';

        if ($schoolUsername === '') {
            $this->flash('error', 'School Username not set.');
            $this->redirect($this->basePath);
        }

        $username = strtolower($admNo) . "@" . strtolower($schoolUsername);

        $studentData = [
            'name' => $fullName,
            'username' => $username,
            'role' => 'Student',
            'upi' => $upi,
            'admission_number' => $admNo,
            'status' => $status
        ];

        // Only assign default password hash on creation
        if (empty($studentId)) {
            $studentData['password'] = password_hash($admNo, PASSWORD_DEFAULT);
        }

        // Handle profile photo upload cleanly
        $profilePath = null;
        if ($profile !== null && $profile['error'] === UPLOAD_ERR_OK) {
            $profilePath = $this->handleFileUpload($profile, 'profiles');
            if ($profilePath) {
                $studentData['profile_photo'] = $profilePath;
            }
        }

        try {
            $saved = !empty($studentId)
                ? $this->students->update($studentId, $studentData)
                : $this->students->create($studentData);

            if (!$saved) {
                if ($profilePath !== null) {
                    $this->deleteFile($profilePath);
                }
                $this->flash('error', 'Unable to save student information. Check that the username and UPI are not already in use.');
                $this->redirect($this->basePath);
            }

            if ($existingStudent && $profilePath !== null && !empty($existingStudent['profile_photo'])) {
                $this->deleteFile($existingStudent['profile_photo']);
            }

            $this->flash('success', !empty($studentId) ? 'Updated student info.' : 'Saved student info.');
        } catch (Throwable $th) {
            error_log('Failed to save/update student info: ' . $th->getMessage());
            if ($profilePath !== null) {
                $this->deleteFile($profilePath);
            }
            $this->flash('error', 'An error occurred while saving student information.');
        }

        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $studentId = filter_var($id, FILTER_VALIDATE_INT);
        $student = $studentId ? $this->students->find($studentId) : null;

        if (!$student || ($student['role'] ?? null) !== 'Student') {
            $this->flash('error', 'Student not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->students->delete($studentId)) {
            $this->flash('error', 'Unable to delete the student.');
            $this->redirect($this->basePath);
        }

        if (!empty($student['profile_photo'])) {
            $this->deleteFile($student['profile_photo']);
        }

        $this->flash('success', 'Student successfully deleted.');
        $this->redirect($this->basePath);
    }
}
