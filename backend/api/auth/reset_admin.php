<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// First, delete existing admin if exists
$query = "DELETE FROM users WHERE email = 'admin@hu.edu'";
$stmt = $db->prepare($query);
$stmt->execute();

// Create new admin with fresh password hash
$admin_password = password_hash('Admin@123', PASSWORD_DEFAULT);
$query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($query);

if ($stmt->execute(['System Administrator', 'admin@hu.edu', $admin_password, 'admin'])) {
    echo json_encode([
        "success" => true,
        "message" => "Admin user reset successfully",
        "credentials" => [
            "email" => "admin@hu.edu",
            "password" => "Admin@123"
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to reset admin user"
    ]);
}
?> 