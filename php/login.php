<?php
header("Content-Type: application/json");

// 1️⃣ Database connection
$host = "localhost";
$dbname = "glowmart";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

// 2️⃣ Validate input
if (!isset($_POST['email'], $_POST['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// 3️⃣ Fetch user by email
$stmt = $pdo->prepare(
    "SELECT user_id, fullname, password FROM users WHERE email = ?"
);
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 4️⃣ Check user & verify password
if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email or password"
    ]);
    exit;
}

// 5️⃣ Start session
session_start();
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['fullname'] = $user['fullname'];

// 6️⃣ Success response
echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user" => [
        "id" => $user['user_id'],
        "fullname" => $user['fullname']
    ]
]);
