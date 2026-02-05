<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$page_title = "Pesanan Berhasil";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Success Message -->
        <div class="card border-success">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h2 class="text-success mb-3">Pesanan Berhasil Dibuat!</h2>
                <p class="lead">Terima kasih atas pesanan Anda. Kami akan segera memproses pesanan Anda.</p>
                <div class="alert alert-info">
                    <strong>Nomor Pesanan: #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                </div>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Pesanan</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informasi Pesanan</h6>
                        <p class="mb-1"><strong>Nomor Pesanan:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-warning">Menunggu Konfirmasi</span>
                        </p>
                        <p class="mb-1"><strong>Total:</strong> 
                            <span class="text-primary fw-bold"><?php echo format_rupiah($order['total_amount']); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Pengiriman</h6>
                        <p class="mb-1"><strong>Nama:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p class="mb-1"><strong>Telepon:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p class="mb-1"><strong>Alamat:</strong></p>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                <div class="mb-4">
                    <h6>Catatan Pesanan</h6>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Order Items -->
                <h6>Produk yang Dipesan</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['image'] ? 'uploads/' . $item['image'] : 'assets/images/no-image.jpg'; ?>" 
                                             class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo format_rupiah($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo format_rupiah($item['price'] * $item['quantity']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Payment Instructions -->
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark">
                    <i class="fas fa-credit-card"></i> Instruksi Pembayaran
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Penting:</strong> Silakan lakukan pembayaran dalam 24 jam untuk mengkonfirmasi pesanan Anda.
                </div>
                
                <h6>Transfer ke Rekening Berikut:</h6>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="border p-3 rounded">
                            <h6 class="text-primary">Bank Mandiri</h6>
                            <p class="mb-1"><strong>No. Rekening:</strong> 1070020338341</p>
                            <p class="mb-1"><strong>Atas Nama:</strong> Elwina Situmorang</p>
                            <p class="mb-0"><strong>Jumlah:</strong> <?php echo format_rupiah($order['total_amount']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Setelah Transfer:</h6>
                    <ol>
                        <li>Kirim bukti transfer via WhatsApp ke <strong>+62 813 17975623</strong></li>
                        <li>Sertakan nomor pesanan: <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></li>
                        <li>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="text-center mt-4 mb-5">
            <a href="orders.php" class="btn btn-primary me-2">
                <i class="fas fa-list"></i> Lihat Semua Pesanan
            </a>
            <a href="products.php" class="btn btn-outline-primary">
                <i class="fas fa-shopping-bag"></i> Lanjut Belanja
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>