<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$instructor_id = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : null;

if (!$instructor_id) {
    echo json_encode(["success" => false, "message" => "No instructor ID provided."]);
    exit;
}

// Count courses
$stmt = $db->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id = :instructor_id");
$stmt->bindParam(':instructor_id', $instructor_id);
$stmt->execute();
$courses = $stmt->fetchColumn();

// Count students (unique across all courses)
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT ce.student_id)
    FROM courses c
    JOIN course_enrollments ce ON ce.course_id = c.id
    WHERE c.instructor_id = :instructor_id
");
$stmt->bindParam(':instructor_id', $instructor_id);
$stmt->execute();
$students = $stmt->fetchColumn();

// Count resources uploaded by this instructor
$stmt = $db->prepare("SELECT COUNT(*) FROM resources WHERE uploaded_by = :instructor_id");
$stmt->bindParam(':instructor_id', $instructor_id);
$stmt->execute();
$resources = $stmt->fetchColumn();

// Count discussions created by this instructor
$stmt = $db->prepare("SELECT COUNT(*) FROM forums WHERE created_by = :instructor_id");
$stmt->bindParam(':instructor_id', $instructor_id);
$stmt->execute();
$discussions = $stmt->fetchColumn();

echo json_encode([
    "success" => true,
    "courses" => $courses,
    "students" => $students,
    "resources" => $resources,
    "discussions" => $discussions
]);
?> 