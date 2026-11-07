<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Schools;
use App\Models\Users;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
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
        $profile = $_FILES['profile'] ?? null;

        $normalizeDate = static function (mixed $value): ?string {
            if (!is_string($value) || ($value = trim($value)) === '') {
                return null;
            }

            foreach (['!Y-m-d', '!Y/m/d', '!d/m/Y', '!d-m-Y'] as $format) {
                $date = \DateTimeImmutable::createFromFormat($format, $value);
                $errors = \DateTimeImmutable::getLastErrors();
                $inputFormat = substr($format, 1);

                if (
                    $date !== false
                    && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                    && $date->format($inputFormat) === $value
                ) {
                    return $date->format('Y-m-d');
                }
            }

            return null;
        };

        $dob = $normalizeDate($_POST['dob'] ?? null);
        $doa = $normalizeDate($_POST['doa'] ?? null);

        if ($fullName === '' || $admNo === '' || $upi === '') {
            $this->flash('error', 'All fields are required.');
            $this->redirect($this->basePath);
        }

        if ($dob === null || $doa === null) {
            $this->flash('error', 'Enter valid dates of birth and admission.');
            $this->redirect($this->basePath);
        }

        if ($doa < $dob) {
            $this->flash('error', 'Date of admission cannot be before date of birth.');
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
            'dob' => $dob,
            'doa' => $doa,
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

    public function import(): void
    {
        if (!$this->confirmToken($_POST['token'] ?? null)) {
            $this->flash('error', 'Invalid token received.');
            $this->redirect($this->basePath);
        }

        $file = $_FILES['spreadsheet'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please upload a valid spreadsheet file.');
            $this->redirect($this->basePath);
        }

        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            $this->flash('error', 'Invalid file type. Please upload an Excel or CSV file.');
            $this->redirect($this->basePath);
        }

        try {
            // FIXED: Typo corrected from $speadsheet to $spreadsheet
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $sheetData = $sheet->toArray(null, true, true, true);
            $normalizeDate = static function (mixed $value): ?string {
                if (!is_string($value) || ($value = trim($value)) === '') {
                    return null;
                }

                foreach (['!Y-m-d', '!Y/m/d', '!d/m/Y', '!d-m-Y'] as $format) {
                    $date = \DateTimeImmutable::createFromFormat($format, $value);
                    $errors = \DateTimeImmutable::getLastErrors();
                    $inputFormat = substr($format, 1);

                    if (
                        $date !== false
                        && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                        && $date->format($inputFormat) === $value
                    ) {
                        return $date->format('Y-m-d');
                    }
                }

                return null;
            };
            $normalizeSpreadsheetDate = static function (string $coordinate) use ($sheet, $normalizeDate): ?string {
                $cell = $sheet->getCell($coordinate);
                $value = $cell->getValue();

                if (SpreadsheetDate::isDateTime($cell) && is_numeric($value)) {
                    try {
                        return SpreadsheetDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }

                return $normalizeDate($value);
            };

            $school = $this->school->find($this->schoolId());
            $schoolUsername = $school['username'] ?? '';

            if ($schoolUsername === '') {
                $this->flash('error', 'School Username not set. Cannot generate students usernames.');
                $this->redirect($this->basePath);
            }

            $successCount = 0;
            $errorCount = 0;
            $rowCount = 0;

            foreach ($sheetData as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $fullName = trim($row['A'] ?? '');
                $admNo = trim($row['B'] ?? '');
                $upi = trim($row['C'] ?? '');
                $dob = $normalizeSpreadsheetDate('D' . $index);
                $doa = $normalizeSpreadsheetDate('E' . $index);

                if ($fullName === '' && $admNo === '' && $upi === '') {
                    continue;
                }

                $rowCount++;

                if ($fullName === '' || $admNo === '' || $upi === '' || $dob === null || $doa === null || $doa < $dob) {
                    $errorCount++;
                    continue;
                }

                $formattedName = $this->textFormat($fullName, 'title');
                // FIXED: Changed from 'title' to 'upper' for admission numbers
                $formattedAdm = $this->textFormat($admNo, 'upper');
                $formattedUpi = $this->textFormat($upi, 'upper');
                $username = strtolower($formattedAdm) . "@" . strtolower($schoolUsername);

                $studentData = [
                    'name' => $formattedName, // FIXED: Matches your DB column 'full_name'
                    'username' => $username,
                    'password' => password_hash($formattedAdm, PASSWORD_DEFAULT),
                    'role' => 'Student',
                    'upi' => $formattedUpi,
                    'admission_number' => $formattedAdm,
                    'dob' => $dob,
                    'doa' => $doa
                ];

                try {
                    if ($this->students->create($studentData)) {
                        $successCount++;
                    } else {
                        error_log("Failed to import student row {$index}: database insert returned false.");
                        $errorCount++;
                    }
                } catch (\Throwable $th) {
                    error_log("Failed to import student row {$index}: " . $th->getMessage());
                    $errorCount++;
                }
            }

            if ($rowCount === 0) {
                $this->flash('error', 'No student records found in the spreadsheet.');
            } elseif ($errorCount > 0) {
                $this->flash('error', "Import completed with errors: {$successCount} imported, {$errorCount} failed. Check the server logs for database errors.");
            } else {
                $this->flash('success', "Import completed: {$successCount} imported successfully.");
            }
        } catch (\Throwable $th) {
            error_log('Spreadsheet processing error: ' . $th->getMessage());
            $this->flash('error', 'Failed to read the spreadsheet file.');
        }

        $this->redirect($this->basePath);
    }
}
