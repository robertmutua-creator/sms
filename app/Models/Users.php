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
}
