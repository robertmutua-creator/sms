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

    public function save(?int $id = null): void
    {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        // Check if a file was actually uploaded without errors
        $hasNewLogo = isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK;

        // 1. Validate required text fields
        if (empty($code) || empty($name) || empty($username) || empty($address) || empty($status)) {
            $this->flash('error', 'All fields are required.');
            $this->redirect($this->basePath);
            return; // CRITICAL: Stop execution!
        }

        // 2. Validate logo is provided for NEW records
        if (empty($id) && !$hasNewLogo) {
            $this->flash('error', 'School Logo is required for new schools.');
            $this->redirect($this->basePath);
            return; // CRITICAL: Stop execution!
        }

        $schoolData = [
            'code' => $this->textFormat($code, 'upper'),
            'name' => $this->textFormat($name, 'title'),
            'username' => $this->textFormat($username, 'lower'),
            'address' => $address, // Kept normal or use textFormat if preferred
            'status' => $status,
        ];

        // 3. Handle Logo Upload & Replacement logic
        if ($hasNewLogo) {
            $newLogoPath = $this->handleFileUpload($_FILES['logo'], 'logos');

            if (!empty($id)) {
                // Fetch existing school to delete old logo file if it exists
                $currentSchool = $this->schools->find($id);
                if (!empty($currentSchool['logo_path'])) {
                    $this->deleteFile($currentSchool['logo_path']);
                }
            }

            $schoolData['logo_path'] = $newLogoPath;
        }

        // 4. Database Persistence
        if (!empty($id)) {
            $this->schools->update($id, $schoolData);
            $this->flash('success', 'School updated successfully.');
        } else {
            $this->schools->create($schoolData);
            $this->flash('success', 'School created successfully.');
        }

        $this->redirect($this->basePath);
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
            $this->flash('success', 'School succesfully deleted.');
        }
        $this->redirect($this->basePath);
    }
}
