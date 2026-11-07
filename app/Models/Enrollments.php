<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Enrollments extends Model
{
    protected string $table = 'enrollments';

    public function classStudents(int $class_id, int $year_id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT u.id, u.name, u.admission_number, e.id AS enrollment_id, e.status AS enrollment_status FROM users u JOIN enrollments e ON u.id=e.student_id WHERE e.class_id=:class_id AND e.year_id=:year_id AND e.status='active' AND e.school_id=:schoolid");
            $stmt->execute([
                ':class_id' => $class_id,
                ':year_id' => $year_id,
                ':schoolid' => $this->schoolId
            ]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: null;
        } catch (PDOException $e) {
            error_log("Method [classStudents] failed: " . $e->getMessage());
            return null;
        }
    }

    public function unassignedStudents(int $yearid): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT u.id, u.name, u.admission_number FROM users u WHERE u.role='Student' AND u.school_id=:school_id AND NOT EXISTS (SELECT 1 FROM enrollments e WHERE e.student_id=u.id AND e.year_id=:year_id)");
            $stmt->execute([
                ':year_id' => $yearid,
                ':school_id' => $this->schoolId,
            ]);
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("Error in [unassignedStudents] method: " . $e->getMessage());
            return null;
        }
    }
}
