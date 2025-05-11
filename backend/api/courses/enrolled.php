<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$student_id = $_GET['user_id'] ?? null;
if (!$student_id) {
    echo json_encode(["success" => false, "message" => "Missing user_id"]);
    exit;
}

$stmt = $db->prepare("
    SELECT 
        c.*, 
        u.name AS instructor_name,
        (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
    FROM courses c
    JOIN course_enrollments e ON c.id = e.course_id
    JOIN users u ON c.instructor_id = u.id
    WHERE e.student_id = ?
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "courses" => $courses]); 