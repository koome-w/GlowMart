<?php
// admin/feedback_api.php
require_once '../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $sql = "SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON f.user_id = u.user_id ORDER BY f.created_at DESC";
    $res = $conn->query($sql);
    $feedbacks = [];
    while($row = $res->fetch_assoc()) {
        $feedbacks[] = $row;
    }
    echo json_encode($feedbacks);
    exit;
}

echo json_encode(['error'=>'Invalid action']);
