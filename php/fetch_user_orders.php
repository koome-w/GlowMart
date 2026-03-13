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
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare('
        SELECT 
            o.order_id,
            o.order_date,
            o.total_price,
            o.status,
            o.user_id,
            DATE_ADD(o.order_date, INTERVAL 3 DAY) as delivery_date,
            GROUP_CONCAT(
                CONCAT(op.quantity, "x ", p.product_name)
                SEPARATOR ", "
            ) as products
        FROM orders o
        LEFT JOIN order_products op ON o.order_id = op.order_id
        LEFT JOIN products p ON op.product_id = p.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id, o.order_date, o.total_price, o.status, o.user_id
        ORDER BY o.order_date DESC
    ');

    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}