<?php
session_start();
require "config.php"; // your PDO connection

header("Content-Type: application/json");

if(!isset($_SESSION['user_id'])){
    echo json_encode(["count" => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) AS count FROM cart WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(["count" => $row['count'] ?? 0]);
} catch(PDOException $e) {
    echo json_encode(["count" => 0]);
}
