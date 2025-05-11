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
if (!isset($data['forum_id'], $data['user_id'], $data['content'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

try {
    // Insert the reply
    $stmt = $db->prepare("INSERT INTO forum_posts (forum_id, user_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $data['forum_id'],
        $data['user_id'],
        $data['content']
    ]);
    
    // Get the newly inserted reply ID
    $reply_id = $db->lastInsertId();
    
    // Just update the forum's last activity timestamp
    // We won't update a reply count since the column doesn't exist
    $updateForum = $db->prepare("UPDATE forums SET updated_at = NOW() WHERE id = ?");
    $updateForum->execute([$data['forum_id']]);
    
    echo json_encode([
        "success" => true,
        "reply_id" => $reply_id,
        "message" => "Reply posted successfully"
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} 