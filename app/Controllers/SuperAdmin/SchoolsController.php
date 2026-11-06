<?php

namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Models\Schools;
use Throwable;

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
        $data['moduleTitle'] = $school ? 'Edit School: ' . $school['name'] : 'Manage Schools';
        $this->view('superadmin/schools', $data);
    }

    public function save(): void
    {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $logoFile = $_FILES['logo'] ?? null;

        if (empty($code) || empty($name) || empty($username) || empty($address)) {
            $this->flash('error', 'All text fields are required.');
            $this->redirect($this->basePath);
        }

        if ($logoFile === null || ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Logo file is required for new schools.');
            $this->redirect($this->basePath);
        }

        $logoPath = $this->handleFileUpload($logoFile, 'logos');
        if ($logoPath === null) {
            $this->flash('error', 'The school logo could not be uploaded.');
            $this->redirect($this->basePath);
        }

        $schoolData = [
            'code' => $this->textFormat($code, 'upper'),
            'username' => $this->textFormat($username, 'lower'),
            'name' => $this->textFormat($name, 'title'),
            'address' => $address,
            'status' => $status,
            'logo_path' => $logoPath,
        ];

        try {
            if (!$this->schools->create($schoolData)) {
                $this->deleteFile($logoPath);
                $this->flash('error', 'Unable to create the school.');
                $this->redirect($this->basePath);
            }

            $this->flash('success', 'School created successfully.');
            $this->redirect($this->basePath);
        } catch (Throwable $th) {
            $this->deleteFile($logoPath);
            error_log('Save operation failed: ' . $th->getMessage());
            $this->flash('error', 'An error occurred while creating the school.');
            $this->redirect($this->basePath);
        }
    }

    public function update(string $id): void
    {
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

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $logoFile = $_FILES['logo'] ?? null;

        if (empty($code) || empty($name) || empty($username) || empty($address)) {
            $this->flash('error', 'All text fields are required.');
            $this->redirect($this->basePath);
        }

        $schoolData = [
            'code' => $this->textFormat($code, 'upper'),
            'username' => $this->textFormat($username, 'lower'),
            'name' => $this->textFormat($name, 'title'),
            'address' => $address,
            'status' => $status,
        ];

        $logoPath = null;
        if ($logoFile !== null && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $logoPath = $this->handleFileUpload($logoFile, 'logos');
            if ($logoPath === null) {
                $this->flash('error', 'The school logo could not be uploaded.');
                $this->redirect($this->basePath);
            }
            $schoolData['logo_path'] = $logoPath;
        }

        try {
            if (!$this->schools->update($schoolId, $schoolData)) {
                if ($logoPath !== null) {
                    $this->deleteFile($logoPath);
                }
                $this->flash('error', 'Unable to update the school.');
                $this->redirect($this->basePath);
            }

            if ($logoPath !== null && !empty($existingSchool['logo_path'])) {
                $this->deleteFile($existingSchool['logo_path']);
            }

            $this->flash('success', 'School updated successfully.');
            $this->redirect($this->basePath);
        } catch (Throwable $th) {
            if ($logoPath !== null) {
                $this->deleteFile($logoPath);
            }
            error_log('Update operation failed: ' . $th->getMessage());
            $this->flash('error', 'An error occurred while updating the school.');
            $this->redirect($this->basePath);
        }
    }

    public function destroy(string $id): void
    {
        $schoolId = filter_var($id, FILTER_VALIDATE_INT);
        if ($schoolId === false || $schoolId < 1) {
            $this->flash('error', 'Invalid school ID.');
            $this->redirect($this->basePath);
        }

        $school = $this->schools->find($schoolId);
        if ($school) {
            if (!empty($school['logo_path'])) {
                $this->deleteFile($school['logo_path']);
            }

            $this->schools->delete($schoolId);
            $this->flash('success', 'Schools succesfully deleted.');
        }
        $this->redirect($this->basePath);
    }
}
