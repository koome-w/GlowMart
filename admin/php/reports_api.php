<?php
// admin/reports_api.php
require_once '../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'sales') {
    $sql = "SELECT o.*, u.username, p.name as product_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id LEFT JOIN products p ON o.product_id = p.product_id ORDER BY o.order_id DESC";
    $res = $conn->query($sql);
    $orders = [];
    while($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode($orders);
    exit;
}

if ($action === 'sales_csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report.csv"');
    $sql = "SELECT o.order_id, u.username, p.name as product, o.quantity, o.total, o.status, o.order_date FROM orders o LEFT JOIN users u ON o.user_id = u.user_id LEFT JOIN products p ON o.product_id = p.product_id ORDER BY o.order_id DESC";
    $res = $conn->query($sql);
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order ID','User','Product','Quantity','Total','Status','Order Date']);
    while($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['order_id'],$row['username'],$row['product'],$row['quantity'],$row['total'],$row['status'],$row['order_date']]);
    }
    fclose($out);
    exit;
}

if ($action === 'sales_pdf') {
    // Fallback to CSV export (PDF omitted)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report.csv"');
    $sql = "SELECT o.order_id, u.username, p.name as product, o.quantity, o.total, o.status, o.order_date FROM orders o LEFT JOIN users u ON o.user_id = u.user_id LEFT JOIN products p ON o.product_id = p.product_id ORDER BY o.order_id DESC";
    $res = $conn->query($sql);
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order ID','User','Product','Quantity','Total','Status','Order Date']);
    while($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['order_id'],$row['username'],$row['product'],$row['quantity'],$row['total'],$row['status'],$row['order_date']]);
    }
    fclose($out);
    exit;
}

echo json_encode(['error'=>'Invalid action']);
