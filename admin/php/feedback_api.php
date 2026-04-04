<?php
// admin/feedback_api.php

require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {

    if ($action === 'list') {

        $stmt = $pdo->prepare("
            SELECT 
                f.feedback_id,
                f.user_id,
                f.feedback_type,
                f.subject,
                f.feedback_text,
                f.email,
                f.feedback_date,
                u.fullname
            FROM feedbacks f
            LEFT JOIN users u ON f.user_id = u.user_id
            ORDER BY f.feedback_date DESC
        ");

        $stmt->execute();

        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $feedbacks
        ]);
        exit;
    }

    // Invalid action
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action'
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'details' => $e->getMessage() // remove in production
    ]);
}
?>