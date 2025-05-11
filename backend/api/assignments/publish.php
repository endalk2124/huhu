<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';
include_once '../../objects/assignment.php';

$database = new Database();
$db = $database->getConnection();

$assignment = new Assignment($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $assignment->id = $data->id;
    
    if ($assignment->publish()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Assignment was published successfully."
        ]);
    } else {
        http_response_code(503);
        echo json_encode([
            "success" => false,
            "message" => "Unable to publish assignment."
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unable to publish assignment. ID is required."
    ]);
} 