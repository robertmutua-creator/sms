<?php

namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Models\Schools;

class SchoolsController extends Controller
{
    private Schools $schools;
    private string $basePath;

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SuperAdmin')) {
            $this->flash('error', 'Unauthorized Access');
            $this->redirect('/dashboard');
        }
        $this->schools = new Schools();
        $this->basePath = '/superadmin/schools';
    }

    public function index(?string $id = null): void
    {
        $school = [];
        if ($id !== null) {
            $schoolId = filter_var($id, FILTER_VALIDATE_INT);
            if ($schoolId === false || $schoolId < 1) {
                $this->flash('error', 'Invalid school ID.');
                $this->redirect($this->basePath);
            }

            $school = $this->schools->find($schoolId);
            if ($school === null) {
                $this->flash('error', 'School not found.');
                $this->redirect($this->basePath);
            }
        }

        $data['school'] = $school;
        $data['schools'] = $this->schools->findAll();
        $data['token'] = $this->setToken();
        $data['moduleTitle'] = $school ? 'Edit School: ' . $school['name'] : 'Manage Schools';
        $this->view('superadmin/schools', $data);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $schoolId = null;
        $existingSchool = null;
        if ($id !== null) {
            $schoolId = filter_var($id, FILTER_VALIDATE_INT);
            if ($schoolId === false || $schoolId < 1) {
                $this->flash('error', 'Invalid school ID.');
                $this->redirect($this->basePath);
            }

            $existingSchool = $this->schools->find($schoolId);
            if ($existingSchool === null) {
                $this->flash('error', 'School not found.');
                $this->redirect($this->basePath);
            }
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $logoFile = $_FILES['logo'] ?? null;

        if ($code === '' || $name === '' || $username === '' || $address === '' || !in_array($status, ['Active', 'Inactive'], true)) {
            $this->flash('error', 'All fields are required.');
            $this->redirect($this->basePath);
        }

        $hasLogoUpload = $logoFile !== null && $logoFile['error'] !== UPLOAD_ERR_NO_FILE;
        if (!$schoolId && !$hasLogoUpload) {
            $this->flash('error', 'A school logo is required for new schools.');
            $this->redirect($this->basePath);
        }

        $schoolData = [
            'code' => $this->textFormat($code, 'upper'),
            'name' => $this->textFormat($name, 'title'),
            'username' => $this->textFormat($username, 'lower'),
            'address' => $this->textFormat($address, 'upper'),
            'status' => $status,
        ];

        $logoPath = null;
        if ($hasLogoUpload) {
            $imageInfo = $logoFile['error'] === UPLOAD_ERR_OK
                ? getimagesize($logoFile['tmp_name'])
                : false;
            $imageTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $extension = $imageInfo !== false ? ($imageTypes[$imageInfo['mime']] ?? null) : null;

            if ($extension === null || $logoFile['size'] > 5 * 1024 * 1024) {
                $this->flash('error', 'Upload a valid JPEG, PNG, GIF, or WEBP logo no larger than 5 MB.');
                $this->redirect($this->basePath);
            }

            $logoFile['name'] = 'logo.' . $extension;
            $logoPath = $this->handleFileUpload($logoFile, 'logos');
            if ($logoPath === null) {
                $this->flash('error', 'The school logo could not be uploaded.');
                $this->redirect($this->basePath);
            }
            $schoolData['logo_path'] = $logoPath;
        }

        $saved = $schoolId
            ? $this->schools->update($schoolId, $schoolData)
            : $this->schools->create($schoolData);
        if (!$saved) {
            if ($logoPath !== null) {
                $this->deleteFile($logoPath);
            }
            $this->flash('error', 'Unable to save the school. Check that the code and username are not already in use.');
            $this->redirect($this->basePath);
        }

        if ($schoolId && $logoPath !== null && !empty($existingSchool['logo_path'])) {
            $this->deleteFile($existingSchool['logo_path']);
        }

        $this->flash('success', $schoolId ? 'School updated successfully.' : 'School created successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $schoolId = filter_var($id, FILTER_VALIDATE_INT);
        if ($schoolId === false || $schoolId < 1) {
            $this->flash('error', 'Invalid school ID.');
            $this->redirect($this->basePath);
        }

        $school = $this->schools->find($schoolId);
        if (!$school) {
            $this->flash('error', 'School not found.');
            $this->redirect($this->basePath);
        }
        if (!$this->schools->delete($schoolId)) {
            $this->flash('error', 'Unable to delete the school.');
            $this->redirect($this->basePath);
        }

        if (!empty($school['logo_path'])) {
            $this->deleteFile($school['logo_path']);
        }

        $this->flash('success', 'School successfully deleted.');
        $this->redirect($this->basePath);
    }
}
