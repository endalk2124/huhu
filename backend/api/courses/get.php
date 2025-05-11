<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$course_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$course_id) {
    echo json_encode(["success" => false, "message" => "No course ID provided."]);
    exit;
}

// Get course details
$query = "SELECT c.*, u.name as instructor_name
          FROM courses c
          JOIN users u ON c.instructor_id = u.id
          WHERE c.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $course_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo json_encode(["success" => false, "message" => "Course not found."]);
    exit;
}

// Get enrolled students
$students_query = "SELECT u.id, u.name, u.email
                   FROM course_enrollments cs
                   JOIN users u ON cs.student_id = u.id
                   WHERE cs.course_id = :id";
$students_stmt = $db->prepare($students_query);
$students_stmt->bindParam(':id', $course_id);
$students_stmt->execute();
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

$course['students_list'] = $students;

echo json_encode(["success" => true, "course" => $course]);
?> 