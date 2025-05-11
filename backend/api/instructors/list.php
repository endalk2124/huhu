<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, name, email, created_at, updated_at FROM users WHERE role = 'instructor'";
$stmt = $db->prepare($query);
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["instructors" => $instructors]);
?> 