<?php
session_start();
require "config.php"; // defines $pdo
header("Content-Type: application/json");

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Fetch cart items
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart)) {
        throw new Exception("Cart is empty");
    }

    $total = 0;

    // Check stock and calculate total
    foreach ($cart as $item) {
        $stmt = $pdo->prepare("SELECT price, quantity FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $item['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found: ID {$item['product_id']}");
        }

        if ($product['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID {$item['product_id']}");
        }

        $total += $product['price'] * $item['quantity'];
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':total' => $total
    ]);
    $order_id = $pdo->lastInsertId();

    // Insert order items & update product stock
    foreach ($cart as $item) {
        // Get latest price
        $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $item['product_id']]);
        $price = $stmt->fetchColumn();

        // Insert into order_items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (:order_id, :product_id, :quantity, :price)
        ");
        $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':price' => $price
        ]);

        // Update product stock
        $stmt = $pdo->prepare("
            UPDATE products 
            SET quantity = quantity - :quantity 
            WHERE product_id = :product_id
        ");
        $stmt->execute([
            ':quantity' => $item['quantity'],
            ':product_id' => $item['product_id']
        ]);
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "order_id" => $order_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
