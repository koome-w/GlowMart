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

// Get the data sent from the front-end (product_id)
$data = json_decode(file_get_contents("php://input"), true);

// Validate input (check if product_id is provided)
if (!isset($data['product_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

// Debug: Log the received data
error_log("Removing product ID: " . $data['product_id']);

// Prepare and execute the query to remove the product from the cart
try {
    $stmt = $pdo->prepare("
        DELETE FROM cart 
        WHERE user_id = :user_id AND product_id = :product_id
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $data['product_id']
    ]);

    // Check if any rows were affected (i.e., the product was removed)
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Product removed from cart"
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
        "message" => "Failed to remove cart item"
    ]);
}
