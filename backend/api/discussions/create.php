<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['title'], $data['description'], $data['course_id'], $data['created_by'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$query = "INSERT INTO discussions (title, description, course_id, created_by, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $db->prepare($query);
$success = $stmt->execute([
    $data['title'],
    $data['description'],
    $data['course_id'],
    $data['created_by']
]);

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to create discussion"]);
} 