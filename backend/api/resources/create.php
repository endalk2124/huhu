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

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$uploaded_by = $_POST['uploaded_by'] ?? '';
$file_path = null;

// Handle file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../uploads/resources/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filename = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        $file_path = 'uploads/resources/' . $filename;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload file."]);
        exit;
    }
}

if ($title && $description && $course_id && $uploaded_by) {
    $query = "INSERT INTO resources (title, description, course_id, uploaded_by, file_path, created_at) VALUES (:title, :description, :course_id, :uploaded_by, :file_path, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':uploaded_by', $uploaded_by);
    $stmt->bindParam(':file_path', $file_path);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save resource."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
}
?> 