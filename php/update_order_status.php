<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;
$status = $data['status'] ?? null;
$transaction_id = $data['transaction_id'] ?? null;

if (!$order_id || !$status) {
    echo json_encode(['status' => 'error', 'message' => 'Missing order_id or status']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE orders SET status = :status, transaction_id = :transaction_id WHERE order_id = :order_id');
    $stmt->execute([
        ':status' => $status,
        ':transaction_id' => $transaction_id,
        ':order_id' => $order_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or already updated']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>