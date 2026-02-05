<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

$page_title = "Keranjang Belanja";
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item active">Keranjang</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart"></i> Keranjang Belanja
                    <span class="badge bg-primary ms-2"><?php echo count($cart_items); ?> item</span>
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Keranjang Anda kosong</h5>
                        <p class="text-muted">Belum ada produk yang ditambahkan ke keranjang</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Mulai Belanja
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['image'] ? 'uploads/' . $item['image'] : 'assets/images/no-image.jpg'; ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="height: 80px; object-fit: cover;">
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted small mb-0">
                                    Harga: <?php echo format_rupiah($item['price']); ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    Stok tersedia: <?php echo $item['stock']; ?>
                                </p>
                            </div>
                            <div class="col-md-2">
                                <div class="quantity-selector">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)"
                                            <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>
                                            title="Kurangi jumlah">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="quantity-display">
                                        <?php echo $item['quantity']; ?>
                                    </div>
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                            <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>
                                            title="Tambah jumlah">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block text-center mt-2">
                                    <i class="fas fa-box"></i> Stok: <?php echo $item['stock']; ?>
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong><?php echo format_rupiah($item['price'] * $item['quantity']); ?></strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="removeFromCart(<?php echo $item['id']; ?>)"
                                        title="Hapus dari keranjang">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-outline-danger" onclick="clearCart()">
                                    <i class="fas fa-trash"></i> Kosongkan Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($cart_items)): ?>
    <div class="col-lg-4">
        <div class="card order-summary">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Pesanan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal (<?php echo count($cart_items); ?> item)</span>
                    <span><?php echo format_rupiah($total_amount); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkos Kirim</span>
                    <span class="text-muted">Dihitung di checkout</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total</strong>
                    <strong class="text-primary"><?php echo format_rupiah($total_amount); ?></strong>
                </div>
                
                <div class="d-grid">
                    <a href="checkout.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-credit-card"></i> Checkout
                    </a>
                </div>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Transaksi aman dan terpercaya
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Promo Code -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Kode Promo</h6>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Masukkan kode promo">
                    <button class="btn btn-outline-secondary" type="button">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(cartId, quantity) {
    // Validasi quantity
    if (quantity < 1) {
        quantity = 1;
    }
    
    // Tambahkan loading state
    const quantityDisplay = document.querySelector(`[data-cart-id="${cartId}"] .quantity-display`);
    if (quantityDisplay) {
        quantityDisplay.style.opacity = '0.5';
        quantityDisplay.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    // Update quantity
    updateCartQuantity(cartId, quantity);
}

function clearCart() {
    if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
        fetch('clear_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengosongkan keranjang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>