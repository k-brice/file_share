<?php
/**
 * Database Configuration (PDO)
 * Edit these settings for your EC2 or Local server.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'file_share'); 
define('DB_USER', 'root');
define('DB_PASS', '');

function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
        $pdo->exec("USE `" . DB_NAME . "`");

        // Users Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('user', 'admin') DEFAULT 'user',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Migration: Check for 'role' column
        try {
            $pdo->query("SELECT role FROM users LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password");
        }

        // Migration: Check for 'name' column (it might be 'fullname' from the old project)
        try {
            $pdo->query("SELECT name FROM users LIMIT 1");
        } catch (Exception $e) {
            // Check if 'fullname' exists, then rename it. Otherwise just add 'name'.
            try {
                $pdo->query("SELECT fullname FROM users LIMIT 1");
                $pdo->exec("ALTER TABLE users CHANGE COLUMN fullname name VARCHAR(255) NOT NULL");
            } catch (Exception $e2) {
                $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(255) NOT NULL AFTER id");
            }
        }

        // Seed: Default Admin Account
        $adminEmail = 'admin@gmail.com';
        $adminPass  = password_hash('admin123', PASSWORD_DEFAULT);
        $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkAdmin->execute([$adminEmail]);
        
        if (!$checkAdmin->fetch()) {
            $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
                ->execute(['k-brice', $adminEmail, $adminPass, 'admin']);
        } else {
            $pdo->prepare("UPDATE users SET name = 'k-brice', role = 'admin' WHERE email = ?")
                ->execute([$adminEmail]);
        }

        // Files Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `files` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_size` BIGINT NOT NULL,
            `file_type` VARCHAR(100) NOT NULL,
            `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Migration: Ensure all required columns exist
        $migrations = [
            'user_id'     => "ALTER TABLE files ADD COLUMN user_id INT NULL AFTER id",
            'stored_name' => "ALTER TABLE files ADD COLUMN stored_name VARCHAR(255) NOT NULL AFTER original_name",
            'file_path'   => "ALTER TABLE files ADD COLUMN file_path VARCHAR(255) NOT NULL AFTER stored_name",
            'upload_date' => "ALTER TABLE files ADD COLUMN upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ];

        foreach ($migrations as $column => $sql) {
            try {
                $pdo->query("SELECT $column FROM files LIMIT 1");
            } catch (Exception $e) {
                $pdo->exec($sql);
            }
        }

        // Final Cleanup: Drop legacy columns that might block inserts (like unique_id from old project)
        $legacyColumns = ['unique_id', 'server_path'];
        foreach ($legacyColumns as $col) {
            try {
                $pdo->query("SELECT $col FROM files LIMIT 1");
                $pdo->exec("ALTER TABLE files DROP COLUMN $col");
            } catch (Exception $e) {
                // Column already gone or never existed
            }
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        die("Database setup failed. Please check config/database.php");
    }
}
