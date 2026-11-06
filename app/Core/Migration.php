<?php

namespace App\Core;

use PDOException;

class Migration
{
    public function __construct()
    {
        $this->run();
    }
    private static function run(): void
    {
        $db = Database::getConnection();

        $statements = [
            "CREATE TABLE IF NOT EXISTS schools (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                username VARCHAR(100) UNIQUE NOT NULL,
                address TEXT NOT NULL,
                logo_path VARCHAR(255) DEFAULT NULL,
                status ENUM('Active', 'Inactive') DEFAULT 'Active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS users(
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE DEFAULT NULL,
                password VARCHAR(255) NOT NULL,
                profile_photo VARCHAR(255) DEFAULT NULL,
                role ENUM('SuperAdmin', 'SchoolAdmin', 'Teacher', 'Student') NOT NULL,
                upi VARCHAR(100) DEFAULT NULL UNIQUE,
                admission_number VARCHAR(50) DEFAULT NULL,
                status ENUM('Active', 'Inactive') DEFAULT 'Active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
                UNIQUE KEY unique_school_admission (school_id, admission_number)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        ];

        foreach ($statements as $sql) {
            try {
                $db->exec($sql);
                error_log("Running " . $sql . ";");
            } catch (PDOException $e) {
                error_log("Migration error: " . $e->getMessage());
            }
        }

        try {
            $column = $db->query("SHOW COLUMNS FROM users LIKE 'profile_photo'");
            if ($column->fetch() === false) {
                $db->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER password");
            }
        } catch (PDOException $e) {
            error_log("Profile photo migration error: " . $e->getMessage());
        }
    }
}
