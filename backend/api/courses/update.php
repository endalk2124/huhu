<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->id) &&
    !empty($data->title) &&
    !empty($data->code) &&
    !empty($data->term) &&
    !empty($data->description)
) {
    $query = "UPDATE courses SET title=:title, code=:code, term=:term, description=:description, status=:status WHERE id=:id";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':id', $data->id);
    $stmt->bindParam(':title', $data->title);
    $stmt->bindParam(':code', $data->code);
    $stmt->bindParam(':term', $data->term);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':status', $data->status);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Unable to update course."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Incomplete data."]);
}
?> 