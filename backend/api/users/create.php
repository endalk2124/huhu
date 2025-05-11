<?php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    exit(0);
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get user data from JSON body
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->name, $data->email, $data->role)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Default password (change as needed)
$defaultPassword = password_hash("changeme123", PASSWORD_DEFAULT);

$query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($query);
$success = $stmt->execute([$data->name, $data->email, $defaultPassword, $data->role]);

if ($success) {
    $id = $db->lastInsertId();
    $userQuery = $db->prepare("SELECT id, name, email, role, created_at AS joinDate, updated_at AS lastActive FROM users WHERE id = ?");
    $userQuery->execute([$id]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "Create failed"]);
}
?> 