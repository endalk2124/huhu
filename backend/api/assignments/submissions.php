<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Prevent errors from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

// Check if student_id or assignment_id is provided
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$assignment_id = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : null;

if (!$student_id && !$assignment_id) {
    echo json_encode(["success" => false, "message" => "No student ID or assignment ID provided."]);
    exit;
}

// Check if the assignment_submissions table exists
try {
    // If filtering by student ID
    if ($student_id) {
        $stmt = $db->prepare("SELECT s.*, u.name as student_name, u.email as student_email 
                          FROM assignment_submissions s 
                          LEFT JOIN users u ON s.student_id = u.id 
                          WHERE s.student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id);
    } 
    // If filtering by assignment ID
    else {
        $stmt = $db->prepare("SELECT s.*, u.name as student_name, u.email as student_email 
                          FROM assignment_submissions s 
                          LEFT JOIN users u ON s.student_id = u.id 
                          WHERE s.assignment_id = :assignment_id");
        $stmt->bindParam(':assignment_id', $assignment_id);
    }
    
    $stmt->execute();
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "submissions" => $submissions]);
} catch (Exception $e) {
    // If table doesn't exist or other error
    echo json_encode(["success" => true, "submissions" => [], "message" => "No submissions found."]);
}