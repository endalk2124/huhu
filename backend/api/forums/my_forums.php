<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

// Prevent errors from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Check for user_id parameter
if (!isset($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "Missing user_id parameter"]);
    exit;
}

$userId = $_GET['user_id'];

try {
    // Find all forums where the user has participated (created or replied)
    $stmt = $db->prepare("
        SELECT DISTINCT f.*, u.name as creator_name, u.role as creator_role,
        (SELECT COUNT(*) FROM forum_posts WHERE forum_id = f.id) as replies,
        (SELECT COUNT(DISTINCT user_id) FROM forum_posts WHERE forum_id = f.id) as participants,
        'participated' as relation_type
        FROM forums f
        LEFT JOIN users u ON f.created_by = u.id
        WHERE f.id IN (
            SELECT DISTINCT forum_id FROM forum_posts WHERE user_id = ?
        )
        
        UNION
        
        SELECT f.*, u.name as creator_name, u.role as creator_role,
        (SELECT COUNT(*) FROM forum_posts WHERE forum_id = f.id) as replies,
        (SELECT COUNT(DISTINCT user_id) FROM forum_posts WHERE forum_id = f.id) as participants,
        'created' as relation_type
        FROM forums f
        LEFT JOIN users u ON f.created_by = u.id
        WHERE f.created_by = ?
        
        ORDER BY updated_at DESC
    ");
    
    $stmt->execute([$userId, $userId]);
    $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform the results to match the expected format in the frontend
    $formattedForums = [];
    foreach ($forums as $forum) {
        $formattedForums[] = [
            "id" => $forum['id'],
            "title" => $forum['title'],
            "description" => $forum['description'],
            "category" => $forum['category'] ?? "General",
            "participants" => (int)$forum['participants'],
            "replies" => (int)$forum['replies'] ?? 0,
            "lastActivity" => date('Y-m-d H:i:s', strtotime($forum['updated_at'] ?? $forum['created_at'])),
            "creator" => [
                "name" => $forum['creator_name'] ?? "Unknown",
                "role" => $forum['creator_role'] ?? "Student"
            ],
            "relation_type" => $forum['relation_type'], // 'created' or 'participated'
            "created_by" => $forum['created_by'], // Keep original created_by for filtering
            "createdAt" => $forum['created_at']
        ];
    }
    
    echo json_encode(["success" => true, "forums" => $formattedForums]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error fetching forums: " . $e->getMessage()
    ]);
}
