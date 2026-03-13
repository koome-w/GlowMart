<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : null;

    // Validation
    if (!$order_id || !$product_id || !$rating || !$review_text) {
        throw new Exception('All fields are required');
    }

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }

    if (strlen($review_text) < 5) {
        throw new Exception('Review must be at least 5 characters');
    }

    if (strlen($review_text) > 1000) {
        throw new Exception('Review cannot exceed 1000 characters');
    }

    // Check if user owns this order
    $stmt = $conn->prepare('SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?');
    $stmt->execute([$order_id, $user_id]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Order not found or you do not have permission');
    }

    // Check if review already exists
    $stmt = $conn->prepare('
        SELECT review_id FROM reviews 
        WHERE order_id = ? AND product_id = ?
    ');
    $stmt->execute([$order_id, $product_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('You have already reviewed this product');
    }

    // Insert review
    $stmt = $conn->prepare('
        INSERT INTO reviews (order_id, product_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    
    $stmt->execute([$order_id, $product_id, $rating, $review_text]);

    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
