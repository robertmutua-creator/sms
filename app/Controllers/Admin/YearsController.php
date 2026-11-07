<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Years;

class YearsController extends Controller
{
    private Years $years;
    private string $basePath = '/admin/years';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        $this->years = new Years($this->schoolId());
    }

    public function index(?string $id = null): void
    {
        $year = [];
        if ($id !== null) {
            $yearId = filter_var($id, FILTER_VALIDATE_INT);
            if ($yearId === false || $yearId < 1) {
                $this->flash('error', 'Invalid academic year ID.');
                $this->redirect($this->basePath);
            }

            $year = $this->years->find($yearId);
            if ($year === null) {
                $this->flash('error', 'Academic year not found.');
                $this->redirect($this->basePath);
            }
        }

        $this->view('admins/years', [
            'year' => $year,
            'years' => $this->years->findAll(),
            'token' => $this->setToken(),
            'moduleTitle' => $year ? 'Edit Academic Year: ' . $year['year'] : 'Manage Academic Years',
        ]);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $yearId = $id === null ? null : filter_var($id, FILTER_VALIDATE_INT);
        if ($yearId === false || ($yearId !== null && $yearId < 1)) {
            $this->flash('error', 'Invalid academic year ID.');
            $this->redirect($this->basePath);
        }

        if ($yearId !== null && $this->years->find($yearId) === null) {
            $this->flash('error', 'Academic year not found.');
            $this->redirect($this->basePath);
        }

        $yearInput = $_POST['year'] ?? null;
        $statusInput = $_POST['status'] ?? null;
        if (!is_string($yearInput) || strlen($yearInput) !== 4 || !ctype_digit($yearInput)) {
            $this->flash('error', 'Enter a valid four-digit academic year.');
            $this->redirect($this->basePath);
        }

        $academicYear = filter_var($yearInput, FILTER_VALIDATE_INT);
        if ($academicYear === false || $academicYear < 1000 || $academicYear > 9999) {
            $this->flash('error', 'Academic year must be a four-digit year.');
            $this->redirect($this->basePath);
        }

        if (!is_string($statusInput) || !in_array($statusInput, ['active', 'inactive'], true)) {
            $this->flash('error', 'Select a valid academic year status.');
            $this->redirect($this->basePath);
        }

        $yearData = [
            'year' => $academicYear,
            'status' => $statusInput,
        ];
        $saved = $yearId !== null
            ? $this->years->update($yearId, $yearData)
            : $this->years->create($yearData);

        if (!$saved) {
            $this->flash('error', 'Unable to save the academic year. Check that the year is not already in use.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', $yearId !== null ? 'Academic year updated successfully.' : 'Academic year created successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $yearId = filter_var($id, FILTER_VALIDATE_INT);
        if ($yearId === false || $yearId < 1) {
            $this->flash('error', 'Invalid academic year ID.');
            $this->redirect($this->basePath);
        }

        if ($this->years->find($yearId) === null) {
            $this->flash('error', 'Academic year not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->years->delete($yearId)) {
            $this->flash('error', 'Unable to delete the academic year.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', 'Academic year deleted successfully.');
        $this->redirect($this->basePath);
    }
}
