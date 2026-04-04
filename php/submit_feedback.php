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
    $user_id       = $_SESSION['user_id'];
    $feedback_type = isset($_POST['feedbackType']) ? trim($_POST['feedbackType']) : null;
    $subject       = isset($_POST['subject'])      ? trim($_POST['subject'])      : null;
    $message       = isset($_POST['message'])      ? trim($_POST['message'])      : null;
    $email         = isset($_POST['email'])        ? trim($_POST['email'])        : null;

    // ✅ Validation
    if (empty($feedback_type)) {
        throw new Exception('Please select a feedback type');
    }
    if (empty($subject) || strlen($subject) < 3) {
        throw new Exception('Subject is required (minimum 3 characters)');
    }
    if (empty($message) || strlen($message) < 10) {
        throw new Exception('Message is required (minimum 10 characters)');
    }
    if (strlen($subject) > 100) {
        throw new Exception('Subject cannot exceed 100 characters');
    }
    if (strlen($message) > 1000) {
        throw new Exception('Message cannot exceed 1000 characters');
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    
    // ✅ Insert using named parameters
    $stmt = $pdo->prepare("
        INSERT INTO feedbacks (user_id, feedback_type, subject, feedback_text, feedback_date, email)
        VALUES (:user_id, :feedback_type, :subject, :feedback_text, :feedback_date, :email)
    ");

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':feedback_type', $feedback_type, PDO::PARAM_STR);
    $stmt->bindValue(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindValue(':feedback_text', $message, PDO::PARAM_STR);
    $stmt->bindValue(':feedback_date', date('Y-m-d H:i:s'), PDO::PARAM_STR);

    if (!empty($email)) {
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    } else {
        $stmt->bindValue(':email', null, PDO::PARAM_NULL);
    }

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>