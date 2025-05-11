<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
if (!$student_id) {
    echo json_encode(["success" => false, "message" => "No student ID provided."]);
    exit;
}

$stmt = $db->prepare("SELECT a.* FROM assignments a JOIN course_enrollments ce ON a.course_id = ce.course_id WHERE ce.student_id = :student_id");
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "assignments" => $assignments]); 