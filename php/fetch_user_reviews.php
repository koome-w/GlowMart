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

    // Fetch user reviews with product information
    $stmt = $conn->prepare('
        SELECT 
            r.review_id,
            r.product_id,
            r.order_id,
            r.rating,
            r.review_text,
            r.created_at,
            p.product_name,
            p.product_image
        FROM reviews r
        JOIN products p ON r.product_id = p.product_id
        JOIN orders o ON r.order_id = o.order_id
        WHERE o.user_id = ?
        ORDER BY r.created_at DESC
    ');
    
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'reviews' => $reviews
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
