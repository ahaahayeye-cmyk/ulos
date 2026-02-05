<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$user_id = $_SESSION['user_id'];

if (!$cart_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit();
}

try {
    // Cek apakah cart item milik user
    $stmt = $pdo->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
        exit();
    }
    
    if ($quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Quantity melebihi stok yang tersedia']);
        exit();
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_id]);
    
    echo json_encode(['success' => true, 'message' => 'Keranjang berhasil diupdate']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>