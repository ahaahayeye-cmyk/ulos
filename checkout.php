<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$buy_now_mode = isset($_GET['mode']) && $_GET['mode'] === 'buy_now';
$cart_items = [];

if ($buy_now_mode && isset($_SESSION['buy_now_item'])) {
    $buy_now_item = $_SESSION['buy_now_item'];
    
    $stmt = $pdo->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
    $stmt->execute([$buy_now_item['product_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $cart_items[] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'stock' => $product['stock'],
            'quantity' => min($buy_now_item['quantity'], $product['stock'])
        ];
    }
} else {
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
}

if (empty($cart_items)) {
    header("Location: " . ($buy_now_mode ? 'products.php' : 'cart.php'));
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping_cost = 15000; // Fixed shipping cost
$total_amount = $subtotal + $shipping_cost;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = clean_input($_POST['shipping_address']);
    $phone = clean_input($_POST['phone']);
    $notes = clean_input($_POST['notes']);
    
    if (empty($shipping_address) || empty($phone)) {
        $error = 'Alamat pengiriman dan nomor telepon harus diisi';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, phone, notes) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total_amount, $shipping_address, $phone, $notes]);
            $order_id = $pdo->lastInsertId();
            
            // Create order items and update stock
            foreach ($cart_items as $item) {
                // Insert order item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart or buy now session
            if ($buy_now_mode) {
                unset($_SESSION['buy_now_item']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }
            
            $pdo->commit();
            
            // Redirect to order success page
            header("Location: order_success.php?order_id=" . $order_id);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
        }
    }
}

$page_title = "Checkout";
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?php echo $buy_now_mode ? 'products.php' : 'cart.php'; ?>"><?php echo $buy_now_mode ? 'Produk' : 'Keranjang'; ?></a></li>
        <li class="breadcrumb-item active">Checkout</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-credit-card"></i> Checkout <?php echo $buy_now_mode ? '(Beli Sekarang)' : ''; ?></h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <!-- Shipping Information -->
                    <div class="mb-4">
                        <h5 class="mb-3">Informasi Pengiriman</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <div class="invalid-feedback">
                                Nomor telepon harus diisi
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Alamat Pengiriman *</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                      rows="4" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            <div class="invalid-feedback">
                                Alamat pengiriman harus diisi
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan Pesanan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Catatan tambahan untuk pesanan (opsional)"></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="mb-4">
                        <h5 class="mb-3">Metode Pembayaran</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Pembayaran dilakukan melalui transfer bank. Detail rekening akan diberikan setelah pesanan dikonfirmasi.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Buat Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Order Summary -->
        <div class="card order-summary">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Pesanan</h5>
            </div>
            <div class="card-body">
                <!-- Cart Items -->
                <?php foreach ($cart_items as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo $item['image'] ? 'uploads/' . $item['image'] : 'assets/images/no-image.jpg'; ?>" 
                             class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div>
                            <small class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></small>
                            <br>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                    </div>
                    <small><?php echo format_rupiah($item['price'] * $item['quantity']); ?></small>
                </div>
                <?php endforeach; ?>
                
                <hr>
                
                <!-- Pricing -->
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span><?php echo format_rupiah($subtotal); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkos Kirim</span>
                    <span><?php echo format_rupiah($shipping_cost); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total</strong>
                    <strong class="text-primary"><?php echo format_rupiah($total_amount); ?></strong>
                </div>
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Transaksi aman dan terpercaya
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Shipping Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-truck"></i> Informasi Pengiriman
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li><i class="fas fa-check text-success me-2"></i> Estimasi 2-3 hari kerja</li>
                    <li><i class="fas fa-check text-success me-2"></i> Gratis asuransi pengiriman</li>
                    <li><i class="fas fa-check text-success me-2"></i> Tracking number disediakan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>