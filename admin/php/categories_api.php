<?php
// admin/categories_api.php
require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── LIST CATEGORIES ──────────────────────────────────────────────────────────
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── ADD / EDIT CATEGORY ──────────────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {
    $id   = intval($_POST['category_id'] ?? 0);
    $name = trim($_POST['category_name'] ?? '');

    if (empty($name)) {
        echo json_encode(['error' => 'Category name is required']);
        exit;
    }

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        echo json_encode(['success' => true, 'category_id' => $pdo->lastInsertId()]);
        exit;
    }

    if ($action === 'edit') {
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid category ID']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE categories SET category_name = :name WHERE category_id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// ── DELETE CATEGORY ──────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = intval($_POST['category_id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid category ID']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── FALLBACK ─────────────────────────────────────────────────────────────────
echo json_encode(['error' => 'Invalid action']);