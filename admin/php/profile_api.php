<?php
// admin/profile_api.php
session_start();
require_once '../php/config.php';
header('Content-Type: application/json');

$admin_id = $_SESSION['admin_id'] ?? 1; // fallback for demo
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get') {
    $stmt = $conn->prepare("SELECT username, full_name, email, phone, profile_pic FROM admin WHERE admin_id=?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->bind_result($username, $full_name, $email, $phone, $profile_pic);
    $stmt->fetch();
    echo json_encode([
        'username' => $username,
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'profile_pic' => $profile_pic
    ]);
    exit;
}

if ($action === 'update') {
    $full_name = $_POST['admin_full_name'];
    $email = $_POST['admin_email'];
    $phone = $_POST['admin_phone'];
    $imgPath = '';
    if (isset($_FILES['admin_profile_pic']) && $_FILES['admin_profile_pic']['size'] > 0) {
        $imgName = time().'_'.basename($_FILES['admin_profile_pic']['name']);
        $target = '../assets/'.$imgName;
        if (move_uploaded_file($_FILES['admin_profile_pic']['tmp_name'], $target)) {
            $imgPath = 'assets/'.$imgName;
        }
    }
    if ($imgPath) {
        $stmt = $conn->prepare("UPDATE admin SET full_name=?, email=?, phone=?, profile_pic=? WHERE admin_id=?");
        $stmt->bind_param('ssssi', $full_name, $email, $phone, $imgPath, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET full_name=?, email=?, phone=? WHERE admin_id=?");
        $stmt->bind_param('sssi', $full_name, $email, $phone, $admin_id);
    }
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'change_password') {
    $old = $_POST['admin_old_password'];
    $new = $_POST['admin_new_password'];
    $stmt = $conn->prepare("SELECT password FROM admin WHERE admin_id=?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    if (!password_verify($old, $hashed)) {
        echo json_encode(['error'=>'Old password incorrect']);
        exit;
    }
    $new_hashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin SET password=? WHERE admin_id=?");
    $stmt->bind_param('si', $new_hashed, $admin_id);
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['error'=>'Invalid action']);
