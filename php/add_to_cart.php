<?php
session_start();
require "config.php"; // this defines $pdo
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
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['product_id'], $data['quantity']) || $data['quantity'] <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO cart (user_id, product_id, quantity) 
        VALUES (:user_id, :product_id, :quantity)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $data['product_id'],
        ':quantity' => $data['quantity']
    ]);

    echo json_encode(["status" => "success"]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add to cart"
    ]);
}
