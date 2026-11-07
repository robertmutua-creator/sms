<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Terms;
use App\Models\Years;

class TermsController extends Controller
{
    private Terms $terms;
    private Years $years;
    private string $basePath = '/admin/terms';

    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        $schoolId = $this->schoolId();
        $this->terms = new Terms($schoolId);
        $this->years = new Years($schoolId);
    }

    public function index(?string $id = null): void
    {
        $term = [];
        if ($id !== null) {
            $termId = filter_var($id, FILTER_VALIDATE_INT);
            if ($termId === false || $termId < 1) {
                $this->flash('error', 'Invalid term ID.');
                $this->redirect($this->basePath);
            }

            $term = $this->terms->find($termId);
            if ($term === null) {
                $this->flash('error', 'Term not found.');
                $this->redirect($this->basePath);
            }
        }

        $years = $this->years->findAll();
        $currentYearId = null;
        foreach ($years as $year) {
            if (($year['status'] ?? null) === 'active') {
                $currentYearId = (int) $year['id'];
                break;
            }
        }

        $this->view('admins/terms', [
            'term' => $term,
            'terms' => $this->terms->findAll(),
            'years' => $years,
            'yearLabels' => array_column($years, 'year', 'id'),
            'defaultYearId' => $term['year_id'] ?? $currentYearId,
            'token' => $this->setToken(),
            'moduleTitle' => $term ? 'Edit Term ' . $term['term'] : 'Manage Terms',
        ]);
    }

    public function save(?string $id = null): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $termId = $id === null ? null : filter_var($id, FILTER_VALIDATE_INT);
        if ($termId === false || ($termId !== null && $termId < 1)) {
            $this->flash('error', 'Invalid term ID.');
            $this->redirect($this->basePath);
        }

        if ($termId !== null && $this->terms->find($termId) === null) {
            $this->flash('error', 'Term not found.');
            $this->redirect($this->basePath);
        }

        $termInput = $_POST['term'] ?? null;
        $yearIdInput = $_POST['year_id'] ?? null;
        $statusInput = $_POST['status'] ?? null;
        if (!is_string($termInput) || !in_array($termInput, ['1', '2', '3'], true)) {
            $this->flash('error', 'Select Term 1, Term 2, or Term 3.');
            $this->redirect($this->basePath);
        }

        $yearId = is_string($yearIdInput) ? filter_var($yearIdInput, FILTER_VALIDATE_INT) : false;
        if ($yearId === false || $yearId < 1 || $this->years->find($yearId) === null) {
            $this->flash('error', 'Select a valid academic year.');
            $this->redirect($this->basePath);
        }

        if (!is_string($statusInput) || !in_array($statusInput, ['current', 'completed', 'upcoming'], true)) {
            $this->flash('error', 'Select a valid term status.');
            $this->redirect($this->basePath);
        }

        $termData = [
            'year_id' => $yearId,
            'term' => $termInput,
            'status' => $statusInput,
        ];
        $saved = $termId !== null
            ? $this->terms->update($termId, $termData)
            : $this->terms->create($termData);

        if (!$saved) {
            $this->flash('error', 'Unable to save the term. Check that the selected term does not already exist for that year.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', $termId !== null ? 'Term updated successfully.' : 'Term created successfully.');
        $this->redirect($this->basePath);
    }

    public function destroy(string $id): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $termId = filter_var($id, FILTER_VALIDATE_INT);
        if ($termId === false || $termId < 1) {
            $this->flash('error', 'Invalid term ID.');
            $this->redirect($this->basePath);
        }

        if ($this->terms->find($termId) === null) {
            $this->flash('error', 'Term not found.');
            $this->redirect($this->basePath);
        }

        if (!$this->terms->delete($termId)) {
            $this->flash('error', 'Unable to delete the term.');
            $this->redirect($this->basePath);
        }

        $this->flash('success', 'Term deleted successfully.');
        $this->redirect($this->basePath);
    }
}
