<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

// Adjust the path if needed based on your folder structure!
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../objects/assignment.php';

$database = new Database();
$db = $database->getConnection();
$assignment = new Assignment($db);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id'])) {
    $assignment->id = $data['id'];
    if ($assignment->delete()) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Assignment deleted successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["success" => false, "message" => "Unable to delete assignment."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID is required."]);
} 