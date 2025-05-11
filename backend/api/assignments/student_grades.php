<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
if (!$student_id) {
    echo json_encode(["success" => false, "message" => "No student ID provided."]);
    exit;
}

try {
    // Get all assignments for this student with course info
    // First, get submissions this student has made
    $stmt = $db->prepare("
        SELECT 
            a.id as assignment_id,
            a.title as assignment_title,
            a.description as assignment_description,
            a.course_id,
            a.due_date,
            s.id as submission_id,
            s.content as submission_content,
            s.submission_date,
            s.grade,
            s.feedback,
            s.status as submission_status,
            c.name as course_name,
            c.code as course_code
        FROM 
            assignment_submissions s
        JOIN 
            assignments a ON a.id = s.assignment_id
        LEFT JOIN
            courses c ON a.course_id = c.id
        WHERE 
            s.student_id = :student_id
        ORDER BY 
            s.submission_date DESC
    ");
    
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Print debug info
    error_log("Student ID: " . $student_id);
    error_log("Found assignments: " . count($grades));
    
    // Calculate overall GPA/average (if grades exist)
    $totalGrade = 0;
    $gradeCount = 0;
    
    foreach ($grades as $grade) {
        if (isset($grade['submission_status']) && $grade['submission_status'] === 'graded' && isset($grade['grade']) && is_numeric($grade['grade'])) {
            $totalGrade += floatval($grade['grade']);
            $gradeCount++;
        }
    }
    
    $average = $gradeCount > 0 ? round($totalGrade / $gradeCount, 2) : 0;
    
    // Group assignments by course
    $courseGrades = [];
    foreach ($grades as $grade) {
        $courseId = $grade['course_id'];
        if (!isset($courseGrades[$courseId])) {
            $courseGrades[$courseId] = [
                'course_id' => $courseId,
                'course_name' => $grade['course_name'] ?? 'Unknown Course',
                'course_code' => $grade['course_code'] ?? 'Unknown',
                'assignments' => []
            ];
        }
        
        $courseGrades[$courseId]['assignments'][] = $grade;
    }
    
    echo json_encode([
        "success" => true, 
        "grades" => $grades,
        "course_grades" => array_values($courseGrades),
        "average" => $average,
        "total_graded" => $gradeCount,
        "student_id" => $student_id
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error fetching grades: " . $e->getMessage(),
        "student_id" => $student_id
    ]);
}
