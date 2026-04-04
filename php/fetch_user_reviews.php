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
        r.review_id,
        r.product_id,
        r.rating,
        r.review_text,
        r.review_date,
        p.name AS product_name,
        p.image
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    JOIN orders o ON r.order_id = o.order_id
    WHERE o.user_id = :user_id
    ORDER BY r.review_date DESC
');

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
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