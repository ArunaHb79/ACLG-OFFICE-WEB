<?php
session_start();

function getConnection() {
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $database = 'aclg_hr_db';

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

function ensureDatabaseSchema() {
    $adminConnection = new mysqli('127.0.0.1', 'root', '');
    if ($adminConnection->connect_error) {
        throw new RuntimeException('MySQL connection failed: ' . $adminConnection->connect_error);
    }

    $adminConnection->query("CREATE DATABASE IF NOT EXISTS aclg_hr_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $adminConnection->close();

    $connection = getConnection();

    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            department VARCHAR(100) DEFAULT NULL,
            designation VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(30) DEFAULT NULL,
            join_date DATE DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS leave_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            leave_type VARCHAR(50) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            reason TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS leave_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            leave_id INT NOT NULL,
            changed_by INT DEFAULT NULL,
            old_status VARCHAR(20) NOT NULL,
            new_status VARCHAR(20) NOT NULL,
            note TEXT DEFAULT NULL,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (leave_id) REFERENCES leave_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS leave_balances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            annual_balance INT NOT NULL DEFAULT 20,
            casual_balance INT NOT NULL DEFAULT 8,
            medical_balance INT NOT NULL DEFAULT 10,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ];

    foreach ($queries as $query) {
        $connection->query($query);
    }

    $seedAdmin = $connection->prepare("SELECT id FROM users WHERE username = ?");
    $seedAdmin->bind_param('s', $username);
    $username = 'admin';
    $seedAdmin->execute();
    $seedAdmin->store_result();

    if ($seedAdmin->num_rows === 0) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO users (username, password_hash, role, full_name, email, department, designation, phone, join_date, status) VALUES (?, ?, 'admin', 'System Administrator', 'admin@aclg-hambantota.gov.lk', 'Administration', 'HR Administrator', '0000000000', '2026-01-01', 'Active')");
        $stmt->bind_param('ss', $username, $passwordHash);
        $stmt->execute();
        $stmt->close();
    }

    $seedAdmin->close();
    return $connection;
}

function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function currentUser() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $connection = getConnection();
    $stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}
