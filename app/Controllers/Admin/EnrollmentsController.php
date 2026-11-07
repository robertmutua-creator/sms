<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Classes;
use App\Models\Enrollments;
use App\Models\Years;

class EnrollmentsController extends Controller
{
    private Enrollments $enrollments;
    private Classes $classes;
    private Years $years;
    public function __construct()
    {
        parent::__construct();
        if (!$this->checkRole('SchoolAdmin')) {
            $this->redirect('/dashboard');
        }

        $this->enrollments = new Enrollments($this->schoolId());
        $this->classes = new Classes($this->schoolId());
        $this->years = new Years($this->schoolId());
    }

    public function index(): void
    {
        $classId = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        $yearId = filter_input(INPUT_GET, 'year_id', FILTER_VALIDATE_INT);

        $enrolledStudents = null;
        $unassignedStudents = null;

        if (is_int($classId) && $classId > 0 && is_int($yearId) && $yearId > 0) {
            $enrolledStudents = $this->enrollments->classStudents($classId, $yearId);
            $unassignedStudents = $this->enrollments->unassignedStudents($yearId);
        }

        $this->view('admins/enrollments', [
            'classes' => $this->classes->findAll(),
            'moduleTitle' => 'Class Enrollment Management',
            'years' => $this->years->findAll(),
            'selectedClass' => $classId,
            'selectedYear' => $yearId,
            'enrolledStudents' => $enrolledStudents,
            'unassignedStudents' => $unassignedStudents,
        ]);
    }

    public function store(): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect('/admin/enrollments');
        }

        $classId = filter_var($_POST['class_id'] ?? null, FILTER_VALIDATE_INT);
        $yearId = filter_var($_POST['year_id'] ?? null, FILTER_VALIDATE_INT);
        $studentIds = $this->selectedStudentIds($_POST['student_ids'] ?? null);

        if (!is_int($classId) || $classId < 1 || !is_int($yearId) || $yearId < 1 ||
            $this->classes->find($classId) === null || $this->years->find($yearId) === null ||
            $studentIds === null) {
            $this->flash('error', 'Invalid class, year, or student selection.');
            $this->redirect('/admin/enrollments');
        }

        $availableStudents = $this->enrollments->unassignedStudents($yearId) ?? [];
        $availableStudentIds = array_map('intval', array_column($availableStudents, 'id'));
        $created = 0;
        $failed = false;

        foreach ($studentIds as $studentId) {
            if (!in_array($studentId, $availableStudentIds, true)) {
                $failed = true;
                continue;
            }

            if ($this->enrollments->create([
                'student_id' => $studentId,
                'class_id' => $classId,
                'year_id' => $yearId,
                'status' => 'active',
            ])) {
                $created++;
            } else {
                $failed = true;
            }
        }

        if ($failed) {
            $this->flash('error', $created > 0
                ? "Assigned {$created} student(s); some selections could not be assigned."
                : 'No students were assigned. Check that they are unassigned for this year.');
        } else {
            $this->flash('success', "Assigned {$created} student(s) to the selected class.");
        }

        $this->redirect("/admin/enrollments?class_id={$classId}&year_id={$yearId}");
    }

    public function remove(): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect('/admin/enrollments');
        }

        $classId = filter_var($_POST['class_id'] ?? null, FILTER_VALIDATE_INT);
        $yearId = filter_var($_POST['year_id'] ?? null, FILTER_VALIDATE_INT);
        $studentIds = $this->selectedStudentIds($_POST['student_ids'] ?? null);

        if (!is_int($classId) || $classId < 1 || !is_int($yearId) || $yearId < 1 ||
            $this->classes->find($classId) === null || $this->years->find($yearId) === null ||
            $studentIds === null) {
            $this->flash('error', 'Invalid class, year, or student selection.');
            $this->redirect('/admin/enrollments');
        }

        $enrolledStudents = $this->enrollments->classStudents($classId, $yearId) ?? [];
        $enrollmentIdsByStudent = [];
        foreach ($enrolledStudents as $student) {
            $enrollmentIdsByStudent[(int) $student['id']] = (int) $student['enrollment_id'];
        }

        $removed = 0;
        $failed = false;
        foreach ($studentIds as $studentId) {
            if (!isset($enrollmentIdsByStudent[$studentId])) {
                $failed = true;
                continue;
            }

            if ($this->enrollments->delete($enrollmentIdsByStudent[$studentId])) {
                $removed++;
            } else {
                $failed = true;
            }
        }

        if ($failed) {
            $this->flash('error', $removed > 0
                ? "Removed {$removed} student(s); some selections could not be removed."
                : 'No students were removed. Check that they are enrolled in the selected class and year.');
        } else {
            $this->flash('success', "Removed {$removed} student(s) from the selected class.");
        }

        $this->redirect("/admin/enrollments?class_id={$classId}&year_id={$yearId}");
    }

    private function selectedStudentIds(mixed $value): ?array
    {
        if (!is_array($value) || $value === []) {
            return null;
        }

        $studentIds = [];
        foreach ($value as $id) {
            $studentId = filter_var($id, FILTER_VALIDATE_INT);
            if (!is_int($studentId) || $studentId < 1) {
                return null;
            }
            $studentIds[] = $studentId;
        }

        return array_values(array_unique($studentIds));
    }
}
