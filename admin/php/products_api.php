<?php
// admin/php/products_api.php
require_once '../../php/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── LIST PRODUCTS ────────────────────────────────────────────────────────────
if ($action === 'list') {
    $cat = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $sql = "SELECT p.*, c.category_name AS category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id";

    if ($cat > 0) {
        $sql .= " WHERE p.category_id = :cat ORDER BY p.product_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cat' => $cat]);
    } else {
        $sql .= " ORDER BY p.product_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── LIST CATEGORIES ──────────────────────────────────────────────────────────
if ($action === 'categories') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── ADD / EDIT PRODUCT ───────────────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {

    // Validate required fields
    if (empty($_POST['product_name']) || !isset($_POST['product_price']) || !isset($_POST['product_stock'])) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // Extract and sanitize POST variables
    $name    = trim($_POST['product_name']);
    $cat     = intval($_POST['product_category'] ?? 0);
    $price   = floatval($_POST['product_price']);
    $quantity = intval($_POST['product_stock']);
    //$desc    = trim($_POST['product_description'] ?? '');
    $id      = intval($_POST['product_id'] ?? 0);
    $imgPath = null;

    // Handle image upload
   if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK && $_FILES['product_image']['size'] > 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mimeType = mime_content_type($_FILES['product_image']['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['error' => 'Invalid image type. Only JPG, PNG, GIF, WEBP allowed.']);
        exit;
    }

    $imgName   = time() . '_' . basename($_FILES['product_image']['name']);
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $target = $uploadDir . $imgName;
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
        $imgPath = $imgName; // ✅ clean relative path saved to DB
    } else {
        echo json_encode(['error' => 'Failed to upload image.']);
        exit;
    }
}

    // ── INSERT ───────────────────────────────────────────────────────────────
    if ($action === 'add') {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, category_id, price, quantity, image) 
             VALUES (:name, :cat, :price, :quantity, :img)"
        );
        $stmt->execute([
            ':name'  => $name,
            ':cat'   => $cat,
            ':price' => $price,
            ':quantity' => $quantity,
            //':desc'  => $desc,
            ':img'   => $imgPath,
        ]);
        echo json_encode(['success' => true, 'product_id' => $pdo->lastInsertId()]);
        exit;
    }

    // ── UPDATE ───────────────────────────────────────────────────────────────
    if ($action === 'edit') {
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid product ID']);
            exit;
        }

        if ($imgPath) {
            $stmt = $pdo->prepare(
                "UPDATE products 
                 SET name=:name, category_id=:cat, price=:price, quantity=:quantity, image=:img 
                 WHERE product_id=:id"
            );
            $stmt->execute([
                ':name'  => $name,
                ':cat'   => $cat,
                ':price' => $price,
                ':quantity' => $quantity,
                //':desc'  => $desc,
                ':img'   => $imgPath,
                ':id'    => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE products 
                 SET name=:name, category_id=:cat, price=:price, quantity=:quantity
                 WHERE product_id=:id"
            );
            $stmt->execute([
                ':name'  => $name,
                ':cat'   => $cat,
                ':price' => $price,
                ':quantity' => $quantity,
                //':desc'  => $desc,
                ':id'    => $id,
            ]);
        }

        echo json_encode(['success' => true]);
        exit;
    }
}

// ── DELETE PRODUCT ───────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = intval($_POST['product_id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid product ID']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── FALLBACK ─────────────────────────────────────────────────────────────────
echo json_encode(['error' => 'Invalid action']);