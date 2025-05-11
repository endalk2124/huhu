<?php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

try {
    include_once '../../config/database.php';

    $database = new Database();
    $db = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        throw new Exception("Invalid JSON data received");
    }

    if (
        empty($data->title) ||
        empty($data->code) ||
        empty($data->term) ||
        empty($data->description) ||
        empty($data->instructor_id)
    ) {
        throw new Exception("Missing required fields");
    }

    $query = "INSERT INTO courses (title, code, term, description, instructor_id, status, created_at, updated_at)
              VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";
    $stmt = $db->prepare($query);

    if (!$stmt->execute([
        $data->title,
        $data->code,
        $data->term,
        $data->description,
        $data->instructor_id
    ])) {
        throw new Exception("Failed to create course");
    }

    $course_id = $db->lastInsertId();
    echo json_encode([
        "success" => true, 
        "message" => "Course created successfully",
        "course_id" => $course_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?> 