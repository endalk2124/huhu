<?php
// Configure session cookie (important for localhost)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,        // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'       // Use 'None' + secure only on HTTPS
]);
session_start();

// CORS headers
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized: Please log in first"
    ]);
    exit();
}

// Get incoming JSON data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->course_id)) {
    try {
        // Check if course exists
        $course_check_stmt = $db->prepare("SELECT id FROM courses WHERE id = ?");
        $course_check_stmt->execute([$data->course_id]);

        if ($course_check_stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Course not found"
            ]);
            exit();
        }

        // Check if user is already enrolled
        $enrollment_check_stmt = $db->prepare("SELECT id FROM course_enrollments WHERE student_id = ? AND course_id = ?");
        $enrollment_check_stmt->execute([$_SESSION['user_id'], $data->course_id]);

        if ($enrollment_check_stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Already enrolled in this course"
            ]);
            exit();
        }

        // Enroll the user
        $enroll_stmt = $db->prepare("INSERT INTO course_enrollments (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");

        if ($enroll_stmt->execute([$_SESSION['user_id'], $data->course_id])) {
            // Update courses_enrolled count
            $update_count_stmt = $db->prepare("UPDATE users SET courses_enrolled = courses_enrolled + 1 WHERE id = ?");
            $update_count_stmt->execute([$_SESSION['user_id']]);

            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "Successfully enrolled in the course"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to enroll in the course"
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Server error: " . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Course ID is required"
    ]);
}
$enrollment_check_stmt = $db->prepare("SELECT id FROM course_enrollments WHERE student_id = ? AND course_id = ?");
$enrollment_check_stmt->execute([$_SESSION['user_id'], $data->course_id]);

$enroll_stmt = $db->prepare("INSERT INTO course_enrollments (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
$enroll_stmt->execute([$_SESSION['user_id'], $data->course_id]);