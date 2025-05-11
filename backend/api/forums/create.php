<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['title'], $data['description'], $data['created_by'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

try {
    // 1. Create the forum
    $stmt = $db->prepare("INSERT INTO forums (title, description, created_by, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['created_by']
    ]);
    $forum_id = $db->lastInsertId();

    // 2. Create the first post
    $stmt2 = $db->prepare("INSERT INTO forum_posts (forum_id, user_id, title, content, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt2->execute([
        $forum_id,
        $data['created_by'],
        $data['title'],
        $data['description']
    ]);

    echo json_encode(["success" => true, "forum_id" => $forum_id]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} 