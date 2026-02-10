<?php
session_start();
require "config.php"; // ensure the path is correct

header("Content-Type: application/json");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the data sent from the front-end
$data = json_decode(file_get_contents("php://input"), true);

// Validate input (check if product_id and quantity are provided, and if quantity is a positive number)
if (!isset($data['product_id'], $data['quantity']) || $data['quantity'] < 1) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

// Debug: Log the received data
error_log("Received data: " . print_r($data, true));

// Prepare and execute the query to update the cart quantity
try {
    $stmt = $pdo->prepare("
        UPDATE cart 
        SET quantity = :quantity 
        WHERE user_id = :user_id AND product_id = :product_id
    ");

    $stmt->execute([
        ':quantity' => $data['quantity'],
        ':user_id' => $user_id,
        ':product_id' => $data['product_id']
    ]);

    // Check if the update affected any rows (i.e., the product exists in the cart)
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Cart updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Product not found in cart"
        ]);
    }
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update cart"
    ]);
}
