<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare(
        "SELECT f.*, u.name as creator_name, u.role as creator_role, 
            (SELECT COUNT(*) FROM forum_posts p WHERE p.forum_id = f.id) as post_count 
         FROM forums f 
         LEFT JOIN users u ON f.created_by = u.id 
         ORDER BY f.created_at DESC"
    );
    $stmt->execute();
    $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transform forums to include a creator object
    $forums = array_map(function($forum) {
        $forum['creator'] = [
            'name' => $forum['creator_name'],
            'role' => $forum['creator_role']
        ];
        unset($forum['creator_name'], $forum['creator_role']);
        return $forum;
    }, $forums);

    echo json_encode(["success" => true, "forums" => $forums]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "forums" => [], "message" => $e->getMessage()]);
} 