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
                dob DATE DEFAULT NULL,
                doa DATE DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
                UNIQUE KEY unique_school_admission (school_id, admission_number)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS classes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                code VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
                UNIQUE KEY unique_school_class_code (school_id, code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS subjects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                code VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
                UNIQUE KEY unique_school_subject_code (school_id, code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS years(
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                year INT NOT NULL,
                status ENUM('active','inactive'),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE,
                UNIQUE (school_id, year)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS terms(
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                year_id INT NOT NULL,
                term enum('1','2','3') NOT NULL,
                status enum('current','completed','upcoming'),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE,
                UNIQUE(year_id,term)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS enrollments(
                id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                student_id INT NOT NULL,
                class_id INT NOT NULL,
                year_id INT NOT NULL,
                status enum('active','completed','graduated') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE ON UPDATE CASCADE,
                UNIQUE(student_id,year_id)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
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
