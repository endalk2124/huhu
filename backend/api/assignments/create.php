<?php
// Always return JSON, even on errors
function return_json_error($message, $extra = []) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(array_merge([
        "success" => false,
        "message" => $message
    ], $extra));
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// Helper for error responses
function fail($msg, $extra = []) {
    http_response_code(500);
    echo json_encode(array_merge([
        "success" => false,
        "message" => $msg
    ], $extra));
    exit;
}

// Check includes
$base = dirname(__DIR__, 2); // Adjust if needed
if (!file_exists("$base/config/database.php")) fail("Missing config/database.php");
if (!file_exists("$base/objects/assignment.php")) fail("Missing objects/assignment.php");

include_once "$base/config/database.php";
include_once "$base/objects/assignment.php";

// Check database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) fail("Database connection failed.");
} catch (Throwable $e) {
    fail("Database error: " . $e->getMessage());
}

$assignment = new Assignment($db);

// Accept both FormData (POST) and raw JSON
$data = $_POST;
if (empty($data)) {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
}

// --- VALIDATION ---
$required = ['title', 'description', 'course_id', 'due_date'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing required field: $field",
            "received" => $data
        ]);
        exit;
    }
}

// --- ASSIGNMENT CREATION ---
$assignment->title = $data['title'];
$assignment->description = $data['description'];
$assignment->course_id = $data['course_id'];
$assignment->due_date = $data['due_date'];
$assignment->created_at = date('Y-m-d H:i:s');
$assignment->updated_at = $assignment->created_at;

try {
    if ($assignment->create()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Assignment was created successfully.",
            "assignment" => [
                "id" => $assignment->id,
                "title" => $assignment->title,
                "description" => $assignment->description,
                "course_id" => $assignment->course_id,
                "due_date" => $assignment->due_date,
                "created_at" => $assignment->created_at,
                "updated_at" => $assignment->updated_at
            ]
        ]);
    } else {
        fail("Unable to create assignment. (DB insert failed)");
    }
} catch (Throwable $e) {
    fail("Exception: " . $e->getMessage());
} 