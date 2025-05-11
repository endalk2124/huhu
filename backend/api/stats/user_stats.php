<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeStudents = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$activeInstructors = $db->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
$newRegistrations = $db->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();

echo json_encode([
  "totalUsers" => (int)$totalUsers,
  "activeStudents" => (int)$activeStudents,
  "activeInstructors" => (int)$activeInstructors,
  "newRegistrations" => (int)$newRegistrations
]);
?> 