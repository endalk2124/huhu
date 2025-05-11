<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;
$query = "SELECT id, title, description, created_at, updated_at FROM courses WHERE instructor_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["courses" => $courses]);
?> 