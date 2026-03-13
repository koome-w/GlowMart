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
    $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : null;

    if (!$review_id) {
        throw new Exception('Review ID is required');
    }

    // Check if review belongs to the user by verifying through the order
    $stmt = $conn->prepare('
        SELECT r.review_id FROM reviews r
        JOIN orders o ON r.order_id = o.order_id
        WHERE r.review_id = ? AND o.user_id = ?
    ');
    $stmt->execute([$review_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Review not found or you do not have permission to delete it');
    }

    // Delete the review
    $stmt = $conn->prepare('DELETE FROM reviews WHERE review_id = ?');
    $stmt->execute([$review_id]);

    echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
