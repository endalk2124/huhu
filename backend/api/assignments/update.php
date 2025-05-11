<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

function fail($msg, $extra = []) {
    http_response_code(500);
    echo json_encode(array_merge([
        "success" => false,
        "message" => $msg
    ], $extra));
    exit;
}

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../objects/assignment.php';

$database = new Database();
$db = $database->getConnection();
$assignment = new Assignment($db);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id']) && !empty($data['title']) && !empty($data['description']) && !empty($data['course_id']) && !empty($data['due_date'])) {
    $assignment->id = $data['id'];
    $assignment->title = $data['title'];
    $assignment->description = $data['description'];
    $assignment->course_id = $data['course_id'];
    $assignment->due_date = $data['due_date'];
    if ($assignment->update()) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Assignment updated successfully."]);
    } else {
        fail("Unable to update assignment. (DB error)");
    }
} else {
    http_response_code(400);
    fail("Incomplete data.", ["received" => $data]);
} 