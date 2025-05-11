<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();

// Get student ID from query parameters - default to 1 if not provided
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : 1;
$assignment_id = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : 5;
$grade = isset($_GET['grade']) ? $_GET['grade'] : 87;

try {
    // Check if submission already exists
    $checkStmt = $db->prepare("SELECT id FROM assignment_submissions WHERE student_id = ? AND assignment_id = ?");
    $checkStmt->execute([$student_id, $assignment_id]);
    
    if ($checkStmt->rowCount() > 0) {
        // Update existing submission
        $submission = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $updateStmt = $db->prepare("
            UPDATE assignment_submissions 
            SET 
                content = 'Sample submission content for testing',
                grade = ?,
                status = 'graded',
                feedback = 'This is sample feedback from the instructor',
                submission_date = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$grade, $submission['id']]);
        
        echo json_encode([
            "success" => true,
            "message" => "Updated existing submission with ID: " . $submission['id'],
            "submission_id" => $submission['id'],
            "student_id" => $student_id,
            "assignment_id" => $assignment_id,
            "grade" => $grade
        ]);
    } else {
        // Insert new submission
        $insertStmt = $db->prepare("
            INSERT INTO assignment_submissions 
            (assignment_id, student_id, content, grade, status, feedback, submission_date) 
            VALUES (?, ?, 'Sample submission content for testing', ?, 'graded', 'This is sample feedback from the instructor', NOW())
        ");
        $insertStmt->execute([$assignment_id, $student_id, $grade]);
        
        $newId = $db->lastInsertId();
        
        echo json_encode([
            "success" => true,
            "message" => "Created new submission with ID: " . $newId,
            "submission_id" => $newId,
            "student_id" => $student_id,
            "assignment_id" => $assignment_id,
            "grade" => $grade
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "student_id" => $student_id
    ]);
}
