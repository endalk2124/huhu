<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

// Prevent PHP from showing errors in the output
error_reporting(0);
ini_set('display_errors', 0);

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // Get recent forum posts with user details and forum title
    $stmt = $db->prepare("
        SELECT 
            fp.id, 
            fp.forum_id, 
            fp.user_id, 
            fp.content, 
            fp.created_at,
            u.name AS user_name,
            u.role AS user_role,
            f.title AS forum_title
        FROM 
            forum_posts fp
        JOIN 
            users u ON fp.user_id = u.id
        JOIN 
            forums f ON fp.forum_id = f.id
        ORDER BY 
            fp.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute();
    $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true, 
        "activity" => $activity
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error fetching forum activity: " . $e->getMessage()
    ]);
}
