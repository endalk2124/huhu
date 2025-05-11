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

// Get all students enrolled in courses taught by this instructor
$query = "
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(DISTINCT ce.course_id) as enrolledCourses
FROM users u
JOIN course_enrollments ce ON ce.student_id = u.id
JOIN courses c ON ce.course_id = c.id
WHERE c.instructor_id = :instructor_id
GROUP BY u.id
";

$stmt = $db->prepare($query);
$stmt->bindParam(':instructor_id', $instructor_id);
$stmt->execute();

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add placeholder values for attendance, avgGrade, and status if not present
foreach ($students as &$student) {
    if (!isset($student['attendance'])) $student['attendance'] = 0;
    if (!isset($student['avgGrade'])) $student['avgGrade'] = 'N/A';
    if (!isset($student['status'])) $student['status'] = 'active';
}

echo json_encode(["success" => true, "students" => $students]);
?> 