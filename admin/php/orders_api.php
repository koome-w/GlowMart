<?php
// admin/php/orders_api.php
require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── LIST ORDERS ──────────────────────────────────────────────────────────────
if ($action === 'list') {
    $stmt = $pdo->query(
        "SELECT o.order_id, o.total_amount, o.status, o.created_at,
                u.fullname,
                GROUP_CONCAT(p.name SEPARATOR ', ') AS product_name,
                SUM(oi.quantity) AS quantity
         FROM orders o
         LEFT JOIN users u ON o.user_id = u.user_id
         LEFT JOIN order_items oi ON o.order_id = oi.order_id
         LEFT JOIN products p ON oi.product_id = p.product_id
         GROUP BY o.order_id, o.total_amount, o.status, o.created_at, u.fullname
         ORDER BY o.order_id DESC"
    );
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── UPDATE ORDER ─────────────────────────────────────────────────────────────
if ($action === 'update') {
    $id     = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['order_status'] ?? '');

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid order ID']);
        exit;
    }

    if (empty($status)) {
        echo json_encode(['error' => 'Order status is required']);
        exit;
    }

    $allowed = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed)) {
        echo json_encode(['error' => 'Invalid order status']);
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE orders 
         SET status = :status 
         WHERE order_id = :id"
    );
    $stmt->execute([
        ':status' => $status,
        ':id'     => $id,
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// ── FALLBACK ─────────────────────────────────────────────────────────────────
echo json_encode(['error' => 'Invalid action']);