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
if (!isset($data['assignment_id'], $data['student_id'], $data['content'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Missing required fields"
    ]);
    exit;
}

// Validate assignment exists
$checkAssignment = $db->prepare("SELECT id, status FROM assignments WHERE id = ? AND status = 'published'");
$checkAssignment->execute([$data['assignment_id']]);
if ($checkAssignment->rowCount() === 0) {
    echo json_encode([
        "success" => false, 
        "message" => "Assignment not found or not published"
    ]);
    exit;
}

try {
    // Check if submission already exists
    $checkSubmission = $db->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
    $checkSubmission->execute([$data['assignment_id'], $data['student_id']]);
    
    if ($checkSubmission->rowCount() > 0) {
        // Update existing submission
        $submission = $checkSubmission->fetch(PDO::FETCH_ASSOC);
        $stmt = $db->prepare("
            UPDATE assignment_submissions 
            SET content = ?, submission_date = NOW(), updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$data['content'], $submission['id']]);
        
        echo json_encode([
            "success" => true, 
            "message" => "Assignment resubmitted successfully",
            "submission_id" => $submission['id']
        ]);
    } else {
        // Create new submission
        $stmt = $db->prepare("
            INSERT INTO assignment_submissions 
            (assignment_id, student_id, content, submission_date, status, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), 'submitted', NOW(), NOW())
        ");
        $stmt->execute([
            $data['assignment_id'],
            $data['student_id'],
            $data['content']
        ]);
        
        $submission_id = $db->lastInsertId();
        
        echo json_encode([
            "success" => true, 
            "message" => "Assignment submitted successfully",
            "submission_id" => $submission_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error submitting assignment: " . $e->getMessage()
    ]);
}
