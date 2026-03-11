<?php
// admin/payments_api.php
require_once '../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $sql = "SELECT * FROM payment_receipts ORDER BY payment_date DESC";
    $res = $conn->query($sql);
    $payments = [];
    while($row = $res->fetch_assoc()) {
        $payments[] = $row;
    }
    echo json_encode($payments);
    exit;
}

echo json_encode(['error'=>'Invalid action']);
