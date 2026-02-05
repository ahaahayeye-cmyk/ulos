<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Data produk tidak valid']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, name, price, image, stock, status FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product || $product['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan atau tidak aktif']);
        exit();
    }

    if ($product['stock'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Stok produk habis']);
        exit();
    }

    if ($quantity > $product['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Jumlah melebihi stok yang tersedia'
        ]);
        exit();
    }

    $_SESSION['buy_now_item'] = [
        'product_id' => $product['id'],
        'quantity' => $quantity,
        'timestamp' => time()
    ];

    echo json_encode(['success' => true]);
    exit();
} catch (Exception $e) {
    error_log('Buy Now Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan']);
    exit();
}
