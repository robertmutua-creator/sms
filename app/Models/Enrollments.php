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

    public function promoteStudent(int $student, int $targetClass, int $targetYear,): bool
    {
        try {
            $this->db->beginTransaction();
            $updateStmt = $this->db->prepare(
                "UPDATE {$this->table} SET status='completed' WHERE student_id=:student AND school_id=:school AND status='active'"
            );
            $updateStmt->execute([
                ':student' => $student,
                ':school' => $this->schoolId,
            ]);

            $insertStmt = $this->db->prepare("
            INSERT INTO {$this->table}(school_id,student_id,class_id,year_id,status) VALUES(:school,:student,:class,:year,'active') ON DUPLICATE KEY UPDATE class_id=:update_class, status='active';
            ");
            $insertStmt->execute([
                ':school' => $this->schoolId,
                ':student' => $student,
                ':class' => $targetClass,
                ':update_class' => $targetClass,
                ':year' => $targetYear
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            error_log("Method [promoteStudent] failed. " . $e->getMessage());
            return false;
        }
    }
}
