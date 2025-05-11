<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Prevent errors from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

$instructor_id = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : null;
if (!$instructor_id) {
    echo json_encode(["success" => false, "message" => "No instructor ID provided."]);
    exit;
}

try {
    // Get all assignments from the database - instructors should be able to see all assignments
    // This is more flexible than just showing assignments created by this instructor
    $stmt = $db->prepare("SELECT * FROM assignments ORDER BY created_at DESC");
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "assignments" => $assignments]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error fetching assignments: " . $e->getMessage()]);
}
