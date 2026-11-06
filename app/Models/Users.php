<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;
use PDOException;

class Users extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        try {
            $stmt = Database::getConnection()->prepare("SELECT * FROM {$this->table} WHERE username=:username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error [findByUsername]: " . $e->getMessage());
            return null;
        }
    }

    public function findAdminsWithSchoolName(): array
    {
        try {
            $sql = "SELECT users.id, users.name AS full_name, users.username, users.profile_photo, schools.name AS school_name
                    FROM {$this->table} AS users
                    INNER JOIN schools ON schools.id = users.school_id
                    WHERE users.role = :role
                    ORDER BY users.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['role' => 'SchoolAdmin']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error [findAdminsWithSchoolName]: " . $e->getMessage());
            return [];
        }
    }
}
