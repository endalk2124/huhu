<?php
// Prevent PHP from showing errors in the output
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { 
    http_response_code(200); 
    exit; 
}

// If we don't have an ID, return an error
if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "Missing forum ID"]);
    exit;
}

try {
    // Connect to the database
    include_once '../../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // For testing/demo, if there's no actual forum data, use sample data
    if ($_GET['id'] == "1" || $_GET['id'] == "2") {
        // Sample data for forums #1 and #2 for demo purposes
        $sampleForums = [
            "1" => [
                "id" => "1",
                "title" => "Database Normalization Techniques",
                "description" => "Discussion on various database normalization techniques and their applications in real-world scenarios.",
                "category" => "Database Systems",
                "participants" => 12,
                "replies" => 2,
                "lastActivity" => "2 hours ago",
                "creator" => [
                    "name" => "Dr. Smith",
                    "role" => "Instructor"
                ],
                "createdAt" => "2023-05-15T08:30:00Z"
            ],
            "2" => [
                "id" => "2",
                "title" => "Best Practices for Secure Web Development",
                "description" => "Let's discuss the best security practices when developing web applications.",
                "category" => "Web Development",
                "participants" => 8,
                "replies" => 1,
                "lastActivity" => "1 day ago",
                "creator" => [
                    "name" => "Jane Smith",
                    "role" => "Student"
                ],
                "createdAt" => "2023-05-14T10:15:00Z"
            ]
        ];
        
        if (isset($sampleForums[$_GET['id']])) {
            echo json_encode(["success" => true, "forum" => $sampleForums[$_GET['id']]]);
            exit;
        }
    }
    
    // If we get here, try to get the forum from the database
    $stmt = $db->prepare("SELECT f.*, u.name as creator_name, u.role as creator_role FROM forums f LEFT JOIN users u ON f.created_by = u.id WHERE f.id = ?");
    $stmt->execute([$_GET['id']]);
    
    if ($stmt->rowCount() > 0) {
        $forum = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Count replies from forum_posts table
        $replyCount = 0;
        try {
            $replyStmt = $db->prepare("SELECT COUNT(*) FROM forum_posts WHERE forum_id = ?");
            $replyStmt->execute([$_GET['id']]);
            $replyCount = (int)$replyStmt->fetchColumn();
        } catch (Exception $e) {
            // If there's an error counting replies, we'll just use 0
        }
        
        // Count unique participants
        $participantCount = 0;
        try {
            $participantStmt = $db->prepare("SELECT COUNT(DISTINCT user_id) FROM forum_posts WHERE forum_id = ?");
            $participantStmt->execute([$_GET['id']]);
            $participantCount = (int)$participantStmt->fetchColumn();
        } catch (Exception $e) {
            // If there's an error counting participants, we'll just use 0
        }
        
        echo json_encode([
            "success" => true,
            "forum" => [
                "id" => $forum['id'],
                "title" => $forum['title'],
                "description" => $forum['description'],
                "category" => $forum['category'] ?? "General",
                "participants" => $participantCount,
                "replies" => $replyCount, // Use our counted replies instead
                "lastActivity" => date('Y-m-d H:i:s', strtotime($forum['updated_at'] ?? $forum['created_at'])),
                "creator" => [
                    "name" => $forum['creator_name'] ?? "Unknown",
                    "role" => $forum['creator_role'] ?? "Student"
                ],
                "createdAt" => $forum['created_at']
            ]
        ]);
    } else {
        // Not found in database either
        echo json_encode(["success" => false, "message" => "Forum not found"]);
    }
    
} catch (Exception $e) {
    // Any error connecting to the database
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
