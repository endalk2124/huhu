<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM assignments ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$assignments = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $assignments[] = [
        "id" => (string)$row["id"],
        "title" => $row["title"],
        "description" => $row["description"],
        "course_id" => (string)$row["course_id"],
        "due_date" => $row["due_date"],
        "status" => $row["status"],
        "submissions_count" => 0
    ];
}

echo json_encode(["assignments" => $assignments]); 