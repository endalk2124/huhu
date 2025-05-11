<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

if ($result) {
    echo json_encode(["total" => (int)$result['total']]);
} else {
    echo json_encode(["total" => 0]);
}
?> 