<?php
require_once '../db.php'; // include your DB connection

session_start();
$user_id = $_SESSION['user_id']; // or get from JWT/token

// Get all posts (customize query as needed for enrolled courses)
$sql = "SELECT fp.id, fp.title, fp.content, fp.created_at, fp.updated_at, f.title AS forum_title
        FROM forum_posts fp
        JOIN forums f ON fp.forum_id = f.id
        ORDER BY fp.created_at DESC";
$result = $conn->query($sql);

$posts = [];
while ($row = $result->fetch_assoc()) {
    // Check if the student has replied
    $post_id = $row['id'];
    $reply_sql = "SELECT COUNT(*) as cnt FROM forum_comments WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($reply_sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $reply_result = $stmt->get_result()->fetch_assoc();
    $hasReplied = $reply_result['cnt'] > 0;

    $row['hasReplied'] = $hasReplied;
    $posts[] = $row;
}

echo json_encode(['success' => true, 'posts' => $posts]);
?>
