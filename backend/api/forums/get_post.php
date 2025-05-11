<?php
require_once '../db.php';

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Missing post_id']);
    exit;
}

// Fetch post
db_connect();
$sql = "SELECT * FROM forum_posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

// Fetch comments
$comments_sql = "SELECT fc.*, u.name AS user_name FROM forum_comments fc JOIN users u ON fc.user_id = u.id WHERE fc.post_id = ? ORDER BY fc.created_at ASC";
$stmt = $conn->prepare($comments_sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();

$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode(['success' => true, 'post' => $post, 'comments' => $comments]);
?>
