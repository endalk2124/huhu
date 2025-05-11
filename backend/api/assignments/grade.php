<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { 
    http_response_code(200); 
    exit; 
}

// Prevent errors from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($data['submission_id'], $data['instructor_id'], $data['grade'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Missing required fields"
    ]);
    exit;
}

// Validate grade is numeric
if (!is_numeric($data['grade'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Grade must be a number"
    ]);
    exit;
}

try {
    // Verify the submission exists
    $checkSubmission = $db->prepare("SELECT id FROM assignment_submissions WHERE id = ?");
    $checkSubmission->execute([$data['submission_id']]);
    
    if ($checkSubmission->rowCount() === 0) {
        echo json_encode([
            "success" => false, 
            "message" => "Submission not found"
        ]);
        exit;
    }
    
    // Update the submission with grade and feedback
    $stmt = $db->prepare("UPDATE assignment_submissions SET grade = ?, feedback = ?, status = 'graded' WHERE id = ?");
    
    $feedback = isset($data['feedback']) ? $data['feedback'] : null;
    $stmt->execute([$data['grade'], $feedback, $data['submission_id']]);
    
    echo json_encode([
        "success" => true, 
        "message" => "Assignment graded successfully"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error grading assignment: " . $e->getMessage()
    ]);
}
