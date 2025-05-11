<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("SELECT * FROM discussions");
    $stmt->execute();
    $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "discussions" => $discussions]);
} catch (Exception $e) {
    echo json_encode(["success" => true, "discussions" => []]);
} 