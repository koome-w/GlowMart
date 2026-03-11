<?php
// admin/dashboard_data.php
require_once '../../php/config.php';
header('Content-Type: application/json');

// Total sales
$sales = $pdo->query("SELECT IFNULL(SUM(total_amount),0) as total_sales FROM orders WHERE status='Completed'")->fetch(PDO::FETCH_ASSOC);
// Total users
$users = $pdo->query("SELECT COUNT(*) as total_users FROM users")->fetch(PDO::FETCH_ASSOC);
// Total orders
$orders = $pdo->query("SELECT COUNT(*) as total_orders FROM orders")->fetch(PDO::FETCH_ASSOC);
// Pending deliveries
$pending = $pdo->query("SELECT COUNT(*) as pending_deliveries FROM orders WHERE status='Pending'")->fetch(PDO::FETCH_ASSOC);
// Stocks
$stocks = $pdo->query("SELECT IFNULL(SUM(quantity),0) as total_stocks FROM products")->fetch(PDO::FETCH_ASSOC);
// Pie chart: categories by quantity
$catData = $pdo->query("SELECT c.category_name, IFNULL(SUM(p.quantity),0) as quantity FROM categories c LEFT JOIN products p ON c.category_id = p.category_id GROUP BY c.category_id");
$categories = [];
$quantities = [];
while($row = $catData->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['category_name'];
    $quantities[] = (int)$row['quantity'];
}

echo json_encode([
    'sales' => $sales['total_sales'],
    'users' => $users['total_users'],
    'orders' => $orders['total_orders'],
    'pending' => $pending['pending_deliveries'],
    'stocks' => $stocks['total_stocks'],
    'categories' => $categories,
    'quantities' => $quantities
]);