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

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS feedback (
            feedback_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            feedback_type VARCHAR(50) NOT NULL,
            subject VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            email VARCHAR(100),
            status VARCHAR(20) DEFAULT "new",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )
    ');

    $stmt = $pdo->prepare('
        INSERT INTO feedback (user_id, feedback_type, subject, message, email)
        VALUES (?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $user_id,
        $feedback_type,
        $subject,
        $message,
        !empty($email) ? $email : null
    ]);

    echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}