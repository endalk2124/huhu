<?php
// /backend/api/users/update_profile.php
session_start();
require_once '../../db.php'; // Adjust if your db.php is elsewhere
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';

    if ($user_id && $name !== '') {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
        if ($stmt->execute([$name, $bio, $user_id])) {
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Update failed']);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request']);
