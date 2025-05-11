<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$forum_id = isset($_GET['forum_id']) ? intval($_GET['forum_id']) : 0;
if (!$forum_id) {
    echo json_encode(["success" => false, "message" => "Missing forum_id"]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT p.*, u.name as user_name, u.role as user_role FROM forum_posts p JOIN users u ON p.user_id = u.id WHERE p.forum_id = ? ORDER BY p.created_at ASC");
    $stmt->execute([$forum_id]);
    $rawPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the posts to match the expected format in the frontend
    $formattedPosts = [];
    foreach ($rawPosts as $post) {
        $formattedPosts[] = [
            'id' => $post['id'],
            'content' => $post['content'],
            'author' => [
                'name' => $post['user_name'],
                'role' => $post['user_role'] ?? 'Student'
            ],
            'timestamp' => $post['created_at'],
            'likes' => 0 // Default to 0 likes if not yet implemented
        ];
    }
    
    echo json_encode(["success" => true, "posts" => $formattedPosts]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "posts" => [], "message" => $e->getMessage()]);
} 