<?php
// admin/profile_api.php
session_start();
require_once '../../php/config.php';
header('Content-Type: application/json');

$admin_id = $_SESSION['admin_id'] ?? 1;
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get') {
    $stmt = $pdo->prepare("SELECT username, full_name, email, phone, profile_pic FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($admin ?: ['error' => 'Admin not found']);
    exit;
}

if ($action === 'update') {
    $full_name = $_POST['admin_full_name'];
    $email     = $_POST['admin_email'];
    $phone     = $_POST['admin_phone'];
    $imgPath   = '';

    if (isset($_FILES['admin_profile_pic']) && $_FILES['admin_profile_pic']['size'] > 0) {
        $imgName = time() . '_' . basename($_FILES['admin_profile_pic']['name']);
        $target  = '../../assets/' . $imgName;
        if (move_uploaded_file($_FILES['admin_profile_pic']['tmp_name'], $target)) {
            $imgPath = '../assets/' . $imgName;
        }
    }

    if ($imgPath) {
        $stmt = $pdo->prepare("UPDATE admin SET full_name = ?, email = ?, phone = ?, profile_pic = ? WHERE admin_id = ?");
        $stmt->execute([$full_name, $email, $phone, $imgPath, $admin_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET full_name = ?, email = ?, phone = ? WHERE admin_id = ?");
        $stmt->execute([$full_name, $email, $phone, $admin_id]);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'change_password') {
    $old = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $pdo->prepare("SELECT password FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $old !== $row['password']) {
        echo json_encode(['error' => 'Old password incorrect']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
    $stmt->execute([$new, $admin_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);