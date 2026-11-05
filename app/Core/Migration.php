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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];

        foreach ($statements as $sql) {
            try {
                $db->exec($sql);
                error_log("Running " . $sql . ";");
            } catch (PDOException $e) {
                error_log("Migration error: " . $e->getMessage());
            }
        }
    }
}
