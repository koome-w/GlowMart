<?php
require "config.php";
header("Content-Type: application/json");

try {
    $sql = "
        SELECT 
            products.*, 
            categories.category_name
        FROM products
        JOIN categories 
            ON products.category_id = categories.category_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch products"
    ]);
}
