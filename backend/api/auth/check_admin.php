<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch all users with correct field names for frontend
$query = "SELECT id, name, email, role, created_at AS joinDate, updated_at AS lastActive FROM users";
$stmt = $db->prepare($query);
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["users" => $users]);
?>



