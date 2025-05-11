<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "
  SELECT 
    DATE_FORMAT(created_at, '%b') as month,
    SUM(role='student') as Students,
    SUM(role='instructor') as Instructors
  FROM users
  GROUP BY month
  ORDER BY MIN(created_at)
";
$stmt = $db->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?> 