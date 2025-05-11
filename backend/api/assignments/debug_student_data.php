<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();

// Get student ID from query parameters
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

// Create an array to store our results
$result = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'student_id' => $student_id,
    'data' => []
];

try {
    // Check if the assignment_submissions table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'assignment_submissions'");
    $result['tables']['assignment_submissions_exists'] = $tableCheck->rowCount() > 0;
    
    // Get all database tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $result['tables']['all_tables'] = $tables;
    
    // If student_id is provided, get user info
    if ($student_id) {
        $userStmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $userStmt->execute([$student_id]);
        $result['user'] = $userStmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Get all students
        $allStudentStmt = $db->query("SELECT id, name, email FROM users WHERE role = 'student'");
        $result['all_students'] = $allStudentStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get submissions data
    $result['data']['raw_submissions'] = [];
    $submissionsRaw = $db->query("SELECT * FROM assignment_submissions LIMIT 10");
    $result['data']['raw_submissions'] = $submissionsRaw->fetchAll(PDO::FETCH_ASSOC);
    
    if ($student_id) {
        // Get student submissions
        $submissionsStmt = $db->prepare("
            SELECT 
                s.*,
                a.title as assignment_title,
                a.description as assignment_description,
                a.course_id,
                a.due_date,
                c.name as course_name,
                c.code as course_code
            FROM 
                assignment_submissions s
            JOIN 
                assignments a ON s.assignment_id = a.id
            LEFT JOIN
                courses c ON a.course_id = c.id
            WHERE 
                s.student_id = ?
        ");
        $submissionsStmt->execute([$student_id]);
        $result['data']['student_submissions'] = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fix the status field if needed
        $fixStmt = $db->prepare("
            UPDATE assignment_submissions
            SET status = 'graded'
            WHERE student_id = ? AND grade IS NOT NULL AND (status IS NULL OR status != 'graded')
        ");
        $fixStmt->execute([$student_id]);
        $result['data']['fixes_applied'] = $fixStmt->rowCount();
        
        // Get the student submissions again after fixing
        $submissionsStmt->execute([$student_id]);
        $result['data']['fixed_student_submissions'] = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get the schema of the assignment_submissions table
    $schemaStmt = $db->query("DESCRIBE assignment_submissions");
    $result['schema']['assignment_submissions'] = $schemaStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'student_id' => $student_id
    ]);
}
