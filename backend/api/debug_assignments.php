<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include_once './config/database.php';
$database = new Database();
$db = $database->getConnection();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get all submissions
    $submissionsQuery = $db->query("SELECT * FROM assignment_submissions");
    $submissions = $submissionsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all assignments
    $assignmentsQuery = $db->query("SELECT * FROM assignments");
    $assignments = $assignmentsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all users that are students
    $usersQuery = $db->query("SELECT id, name, email, role FROM users WHERE role = 'student'");
    $students = $usersQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "submissions_count" => count($submissions),
        "assignments_count" => count($assignments),
        "students_count" => count($students),
        "submissions" => $submissions,
        "assignments" => $assignments,
        "students" => $students
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error fetching debug data: " . $e->getMessage()
    ]);
}
