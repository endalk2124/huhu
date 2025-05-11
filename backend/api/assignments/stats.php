<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Prevent errors from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

// Check if assignment_id is provided (optional)
$assignment_id = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : null;
$instructor_id = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : null;

if (!$instructor_id) {
    echo json_encode(["success" => false, "message" => "Instructor ID is required"]);
    exit;
}

try {
    // If specific assignment ID is provided
    if ($assignment_id) {
        // Get total possible submissions (total students enrolled in the course)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT ce.student_id) as total_students
            FROM assignments a
            JOIN course_enrollments ce ON a.course_id = ce.course_id
            WHERE a.id = :assignment_id
        ");
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get actual submissions
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT s.id) as submitted,
                COUNT(DISTINCT CASE WHEN s.status = 'graded' THEN s.id END) as graded
            FROM assignment_submissions s
            WHERE s.assignment_id = :assignment_id
        ");
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response = [
            "success" => true,
            "stats" => [
                "submitted" => (int)$stats['submitted'] ?? 0,
                "graded" => (int)$stats['graded'] ?? 0,
                "pending" => $total['total_students'] - ($stats['submitted'] ?? 0)
            ]
        ];
    } else {
        // Get statistics for all assignments (instructors can grade any assignment)
        $stmt = $db->prepare("
            SELECT 
                a.id, 
                COUNT(DISTINCT s.id) as submitted,
                COUNT(DISTINCT CASE WHEN s.status = 'graded' THEN s.id END) as graded,
                (
                    SELECT COUNT(DISTINCT ce.student_id) 
                    FROM course_enrollments ce 
                    WHERE ce.course_id = a.course_id
                ) as total_students
            FROM assignments a
            LEFT JOIN assignment_submissions s ON a.id = s.assignment_id
            GROUP BY a.id
        ");
        $stmt->execute();
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [];
        foreach ($assignments as $assignment) {
            $stats[$assignment['id']] = [
                "submitted" => (int)$assignment['submitted'],
                "graded" => (int)$assignment['graded'],
                "pending" => (int)$assignment['total_students'] - (int)$assignment['submitted']
            ];
        }
        
        $response = [
            "success" => true,
            "stats" => $stats
        ];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    // Handle database errors
    echo json_encode([
        "success" => false, 
        "message" => "Error fetching assignment statistics: " . $e->getMessage()
    ]);
}
