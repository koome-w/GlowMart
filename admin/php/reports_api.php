<?php
// admin/php/reports_api.php
require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'sales') {
    $sql = "SELECT o.order_id, u.fullname, p.name AS product_name, 
                   oi.quantity, oi.price, o.total_amount, o.status, o.created_at
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            ORDER BY o.order_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
    exit;
}

if ($action === 'sales_csv' || $action === 'sales_pdf') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report.csv"');

    $sql = "SELECT o.order_id, u.fullname, p.name AS product_name, 
                   oi.quantity, oi.price, o.total_amount, o.status, o.created_at
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            ORDER BY o.order_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order ID', 'User', 'Product', 'Quantity', 'Price', 'Total Amount', 'Status', 'Date']);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['order_id'],
            $row['fullname'],
            $row['product_name'],
            $row['quantity'],
            $row['price'],
            $row['total_amount'],
            $row['status'],
            $row['created_at']
        ]);
    }

    fclose($out);
    exit;
}

echo json_encode(['error' => 'Invalid action']);