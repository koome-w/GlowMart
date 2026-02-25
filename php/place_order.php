<?php
session_start();
require 'config.php'; // defines $pdo
header('Content-Type: application/json');

// Only allow POST requests for placing orders
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

try {
    // Start DB transaction
    $pdo->beginTransaction();

    // Fetch cart items for user
    $cartStmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = :user_id');
    $cartStmt->execute([':user_id' => $user_id]);
    $cart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart)) {
        throw new Exception('Cart is empty');
    }

    $total = 0.0;

    // Verify stock and compute total
    $productStmt = $pdo->prepare('SELECT price, quantity FROM products WHERE product_id = :product_id');
    foreach ($cart as $item) {
        $productStmt->execute([':product_id' => $item['product_id']]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Product not found: ID ' . (int)$item['product_id']);
        }

        if ((int)$product['quantity'] < (int)$item['quantity']) {
            throw new Exception('Insufficient stock for product ID ' . (int)$item['product_id']);
        }

        $total += (float)$product['price'] * (int)$item['quantity'];
    }

    // Create order (status default is expected to be handled by DB; using pending here)
    $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total)');
    $orderStmt->execute([':user_id' => $user_id, ':total' => $total]);
    $order_id = $pdo->lastInsertId();

    // Insert order items and decrement stock
    $insertItemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
    $updateStockStmt = $pdo->prepare('UPDATE products SET quantity = quantity - :quantity WHERE product_id = :product_id');
    $priceStmt = $pdo->prepare('SELECT price FROM products WHERE product_id = :product_id');

    foreach ($cart as $item) {
        $priceStmt->execute([':product_id' => $item['product_id']]);
        $price = (float) $priceStmt->fetchColumn();

        $insertItemStmt->execute([
            ':order_id'   => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity'   => $item['quantity'],
            ':price'      => $price,
        ]);

        $updateStockStmt->execute([
            ':quantity'   => $item['quantity'],
            ':product_id' => $item['product_id'],
        ]);
    }

    // Clear cart for the user
    $clearStmt = $pdo->prepare('DELETE FROM cart WHERE user_id = :user_id');
    $clearStmt->execute([':user_id' => $user_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    // Roll back if transaction started
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Return generic error message; include exception message for debugging on dev only
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
