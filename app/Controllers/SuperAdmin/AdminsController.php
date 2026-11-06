<?php

namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Models\Schools;
use App\Models\Users;

class AdminsController extends Controller
{
    private Schools $schools;
    private Users $users;
    private string $basePath = '/superadmin/admins';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SuperAdmin')) {
            $this->flash('error', 'Unauthorized Access');
            $this->redirect('/dashboard');
        }

        $this->schools = new Schools();
        $this->users = new Users();
    }

    public function index(?string $id = null): void
    {
        $admin = [];
        if ($id !== null) {
            $adminId = filter_var($id, FILTER_VALIDATE_INT);
            if ($adminId === false || $adminId < 1) {
                $this->flash('error', 'Invalid admin ID.');
                $this->redirect($this->basePath);
            }

            $admin = $this->users->find($adminId);
            if (!$admin || $admin['role'] !== 'SchoolAdmin') {
                $this->flash('error', 'Admin not found.');
                $this->redirect($this->basePath);
            }
        }

        $data = [
            'admins' => $this->users->findAdminsWithSchoolName(),
            'schools' => $this->schools->findAll(),
            'admin' => $admin,
            'token' => $this->setToken(),
            'moduleTitle' => $admin ? 'Edit School Admin' : 'Manage School Admins',
        ];
        $this->view('superadmin/admins', $data);
    }

    public function save(): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = $this->textFormat(trim($_POST['username'] ?? ''), 'lower');
        $schoolId = filter_var($_POST['school_id'] ?? null, FILTER_VALIDATE_INT);
        $profilePhoto = $_FILES['profile_photo'] ?? null;

        if ($fullName === '' || $username === '' || $schoolId === false || $schoolId === null || $schoolId < 1) {
            $this->flash('error', 'Full name, username, and a registered school are required.');
            $this->redirect($this->basePath);
        }

        if ($this->schools->find($schoolId) === null) {
            $this->flash('error', 'The selected school was not found.');
            $this->redirect($this->basePath);
        }

        $imageTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $imageInfo = $profilePhoto !== null && $profilePhoto['error'] === UPLOAD_ERR_OK
            ? getimagesize($profilePhoto['tmp_name'])
            : false;
        $extension = $imageInfo !== false ? ($imageTypes[$imageInfo['mime']] ?? null) : null;

        if ($extension === null || $profilePhoto['size'] > 5 * 1024 * 1024) {
            $this->flash('error', 'Upload a valid JPEG, PNG, GIF, or WEBP profile photo no larger than 5 MB.');
            $this->redirect($this->basePath);
        }

        $profilePhoto['name'] = 'profile.' . $extension;
        $profilePhotoPath = $this->handleFileUpload($profilePhoto, 'profiles');
        if ($profilePhotoPath === null) {
            $this->flash('error', 'The profile photo could not be uploaded.');
            $this->redirect($this->basePath);
        }

        $created = $this->users->create([
            'school_id' => $schoolId,
            'name' => $fullName,
            'username' => $username,
            'password' => password_hash($username, PASSWORD_DEFAULT),
            'role' => 'SchoolAdmin',
            'profile_photo' => $profilePhotoPath,
        ]);

        if (!$created) {
            $this->deleteFile($profilePhotoPath);
            $this->flash('error', 'Unable to create the admin. Check that the username is not already in use.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', 'School admin created. Their default password is their username.');
        $this->redirect($this->basePath);
    }

    public function update(string $id): void
    {
        $adminId = filter_var($id, FILTER_VALIDATE_INT);
        if ($adminId === false || $adminId < 1) {
            $this->flash('error', 'Invalid admin ID.');
            $this->redirect($this->basePath);
        }

        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $existingAdmin = $this->users->find($adminId);
        if (!$existingAdmin || $existingAdmin['role'] !== 'SchoolAdmin') {
            $this->flash('error', 'Admin not found.');
            $this->redirect($this->basePath);
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = $this->textFormat(trim($_POST['username'] ?? ''), 'lower');
        $schoolId = filter_var($_POST['school_id'] ?? null, FILTER_VALIDATE_INT);
        $profilePhoto = $_FILES['profile_photo'] ?? null;

        if ($fullName === '' || $username === '' || $schoolId === false || $schoolId === null || $schoolId < 1) {
            $this->flash('error', 'Full name, username, and a registered school are required.');
            $this->redirect($this->basePath);
        }

        if ($this->schools->find($schoolId) === null) {
            $this->flash('error', 'The selected school was not found.');
            $this->redirect($this->basePath);
        }

        $hasNewPhoto = $profilePhoto !== null && $profilePhoto['error'] !== UPLOAD_ERR_NO_FILE;
        $profilePhotoPath = null;
        if ($hasNewPhoto) {
            $imageTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $imageInfo = $profilePhoto['error'] === UPLOAD_ERR_OK
                ? getimagesize($profilePhoto['tmp_name'])
                : false;
            $extension = $imageInfo !== false ? ($imageTypes[$imageInfo['mime']] ?? null) : null;

            if ($extension === null || $profilePhoto['size'] > 5 * 1024 * 1024) {
                $this->flash('error', 'Upload a valid JPEG, PNG, GIF, or WEBP profile photo no larger than 5 MB.');
                $this->redirect($this->basePath);
            }

            $profilePhoto['name'] = 'profile.' . $extension;
            $profilePhotoPath = $this->handleFileUpload($profilePhoto, 'profiles');
            if ($profilePhotoPath === null) {
                $this->flash('error', 'The profile photo could not be uploaded.');
                $this->redirect($this->basePath);
            }
        }

        $adminData = [
            'school_id' => $schoolId,
            'name' => $fullName,
            'username' => $username,
        ];

        if ($profilePhotoPath !== null) {
            $adminData['profile_photo'] = $profilePhotoPath;
        }

        if (!$this->users->update($adminId, $adminData)) {
            if ($profilePhotoPath !== null) {
                $this->deleteFile($profilePhotoPath);
            }
            $this->flash('error', 'Unable to update the admin. Check that the username is not already in use.');
            $this->redirect($this->basePath);
        }

        if ($profilePhotoPath !== null && !empty($existingAdmin['profile_photo'])) {
            $this->deleteFile($existingAdmin['profile_photo']);
        }

        $this->flash('success', 'School admin updated successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $adminId = filter_var($id, FILTER_VALIDATE_INT);
        if ($adminId === false || $adminId < 1) {
            $this->flash('error', 'Invalid admin ID.');
            $this->redirect($this->basePath);
        }

        $admin = $this->users->find($adminId);
        if (!$admin || $admin['role'] !== 'SchoolAdmin') {
            $this->flash('error', 'Admin not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->users->delete($adminId)) {
            $this->flash('error', 'Unable to delete the admin.');
            $this->redirect($this->basePath);
        }

        if (!empty($admin['profile_photo'])) {
            $this->deleteFile($admin['profile_photo']);
        }

        $this->flash('success', 'School admin deleted successfully.');
        $this->redirect($this->basePath);
    }
}
