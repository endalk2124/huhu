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
if (!isset($data->id, $data->name, $data->email, $data->role)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
$stmt = $db->prepare($query);
$success = $stmt->execute([$data->name, $data->email, $data->role, $data->id]);

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
?> 