<?php
// admin/payments_api.php
require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $sql = "SELECT id, order_id, user_id, phone, amount, mpesa_receipt, status, created_at 
            FROM payments
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($payments);
    exit;
}

echo json_encode(['error' => 'Invalid action']);