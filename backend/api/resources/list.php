<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT r.id, r.title, r.description, r.file_path, r.created_at, c.title as course, u.name as uploaded_by
          FROM resources r
          JOIN courses c ON r.course_id = c.id
          JOIN users u ON r.uploaded_by = u.id
          ORDER BY r.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["resources" => $resources]);
?> 