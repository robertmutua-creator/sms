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

    public function index(): void
    {
        $data['school'] = [];
        $data['schools'] = $this->schools->findAll();
        $data['moduleTitle'] = 'Manage Schools';
        $this->view('superadmin/schools/index', $data);
    }

    public function save(): void
    {
        $id = $_POST['id'] ?? null;
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $logoFile = $_FILES['logo'] ?? null;

        // Handle new file upload if provided
        $logoPath = $this->handleFileUpload($logoFile, 'logos');

        // Validation for new schools (logo required)
        if (empty($id) && empty($logoPath)) {
            $this->flash('error', 'Logo file is required for new schools.');
            $this->redirect($this->basePath . '/save');
            return;
        }

        // General text fields validation
        if (empty($code) || empty($name) || empty($username) || empty($address)) {
            $this->flash('error', 'All text fields are required.');
            $this->redirect($this->basePath . (empty($id) ? '/save' : '/edit/' . $id));
        }

        $schoolData = [
            'code' => $this->textFormat($code, 'upper'),
            'username' => $this->textFormat($username, 'lower'),
            'name' => $this->textFormat($name, 'title'),
            'address' => $address,
            'status' => $status,
        ];

        try {
            if (!empty($id)) {
                // Fetch existing record to check for an old logo
                $existingSchool = $this->schools->find($id);

                if (!empty($logoPath)) {
                    $schoolData['logo_path'] = $logoPath;

                    // Delete old logo file if it exists on disk
                    if (!empty($existingSchool['logo_path'])) {
                        $this->deleteFile($existingSchool['logo_path']);
                    }
                }

                $this->schools->update($id, $schoolData);
                $this->flash('success', 'School updated successfully.');
            } else {
                $schoolData['logo_path'] = $logoPath;
                $this->schools->create($schoolData);
                $this->flash('success', 'School created successfully.');
            }

            $this->redirect($this->basePath);
        } catch (Throwable $th) {
            error_log('Save operation failed: ' . $th->getMessage());
            $this->flash('error', 'An error occurred while saving the school.');
            $this->redirect($this->basePath . (empty($id) ? '/save' : '/edit/' . $id));
        }
    }

    public function edit(int $id): void
    {
        $school = $this->schools->find($id);
        if (!$school) {
            $this->flash('error', 'School not found.');
            $this->redirect($this->basePath);
        }

        $data['moduleTitle'] = 'Edit School: ' . htmlspecialchars($school['name']);
        $data['school'] = $school;
        $data['schools'] = $this->schools->findAll();
        $this->view('superadmin/schools', $data);
    }

    public function destroy(int $id): void
    {
        $school = $this->schools->find($id);
        if ($school) {
            if (!empty($school['logo_path'])) {
                $this->deleteFile($school['logo_path']);
            }

            $this->schools->delete($id);
            $this->flash('success', 'Schools succesfully deleted.');
        }
        $this->redirect($this->basePath);
    }
}
