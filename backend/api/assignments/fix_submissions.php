<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();

try {
    // First, let's check what's in the submissions table
    $submissionsQuery = $db->query("SELECT * FROM assignment_submissions");
    $submissions = $submissionsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    $fixCount = 0;
    $errors = [];
    
    // Fix any missing status fields - set them to 'graded' if they have a grade
    foreach ($submissions as $submission) {
        if (isset($submission['id']) && isset($submission['grade']) && $submission['grade'] !== null && (!isset($submission['status']) || $submission['status'] != 'graded')) {
            try {
                $updateStmt = $db->prepare("UPDATE assignment_submissions SET status = 'graded' WHERE id = ? AND grade IS NOT NULL");
                $updateStmt->execute([$submission['id']]);
                $fixCount++;
            } catch (Exception $e) {
                $errors[] = "Error updating submission ID {$submission['id']}: " . $e->getMessage();
            }
        }
    }
    
    // Get the updated submissions
    $updatedQuery = $db->query("SELECT * FROM assignment_submissions");
    $updatedSubmissions = $updatedQuery->fetchAll(PDO::FETCH_ASSOC);
    
    // Try to find the information about a specific student ID
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;
    $studentSubmissions = [];
    
    if ($studentId) {
        $studentStmt = $db->prepare("
            SELECT 
                s.*, 
                a.title as assignment_title,
                a.description as assignment_description,
                u.name as student_name,
                u.email as student_email
            FROM 
                assignment_submissions s
            JOIN 
                assignments a ON s.assignment_id = a.id
            JOIN 
                users u ON s.student_id = u.id
            WHERE 
                s.student_id = ?
        ");
        $studentStmt->execute([$studentId]);
        $studentSubmissions = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        "success" => true,
        "message" => "Data retrieved and fixes attempted",
        "original_submissions" => $submissions,
        "updated_submissions" => $updatedSubmissions,
        "student_submissions" => $studentSubmissions,
        "fixes_applied" => $fixCount,
        "errors" => $errors,
        "student_id" => $studentId
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
