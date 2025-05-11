<?php
session_start();  // ✅ Start the session

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input);

if (!empty($data->email) && !empty($data->password)) {
    $query = "SELECT id, name, email, password, role, is_approved FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$data->email]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        if (password_verify($data->password, $row['password'])) {
            if ($row['is_approved'] != 1) {
                echo json_encode([
                    "success" => false,
                    "message" => "Your account is pending admin approval."
                ]);
                exit;
            }

            // ✅ Save user ID into session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_role'] = $row['role'];

            // ✅ Set cookie explicitly (important on some local setups)
            setcookie(session_name(), session_id(), [
                'path' => '/',
                'domain' => 'localhost',
                'secure' => false,  // set true if using https
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => (int)$row['id'],
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "role" => $row['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
}
?>
