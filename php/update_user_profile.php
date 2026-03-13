<?php
session_start();
header('Content-Type: application/json');

require_once '../php/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

try {
    $user_id  = $_SESSION['user_id'];
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : null;
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : null;

    if (empty($fullname) || strlen($fullname) < 2) {
        throw new Exception('Full name is required and must be at least 2 characters');
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    if (!empty($email)) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email is already in use');
        }
    }

    $stmt = $pdo->prepare('UPDATE users SET fullname = ?, email = ? WHERE user_id = ?');
    $stmt->execute([$fullname, $email ?: null, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}