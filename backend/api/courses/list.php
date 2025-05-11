<?php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    exit(0);
}
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../../config/database.php';

// Start session (optional, but not required for public course list)
session_start();

$database = new Database();
$db = $database->getConnection();



// Query to get courses not yet enrolled by the student
$query = "SELECT 
            c.id, 
            c.title, 
            c.code, 
            c.term, 
            c.status, 
            c.description, 
            c.created_at, 
            c.updated_at, 
            u.id as instructor_id, 
            u.name as instructor_name,
            COUNT(cs.student_id) as students
          FROM courses c
          JOIN users u ON c.instructor_id = u.id
          LEFT JOIN course_enrollments cs ON cs.course_id = c.id
          GROUP BY c.id";

$stmt = $db->prepare($query);
$stmt->execute();

$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["courses" => $courses]);
?>