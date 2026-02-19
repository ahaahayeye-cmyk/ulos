<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$page_title = "Detail Pesanan #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item"><a href="orders.php">Pesanan Saya</a></li>
        <li class="breadcrumb-item active">Detail Pesanan</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Info -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pesanan #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h5>
                    <?php
                    $status_class = '';
                    $status_text = '';
                    switch ($order['status']) {
                        case 'pending':
                            $status_class = 'status-pending';
                            $status_text = 'Menunggu Pembayaran';
                            break;
                        case 'processing':
                            $status_class = 'status-processing';
                            $status_text = 'Diproses';
                            break;
                        case 'shipped':
                            $status_class = 'status-shipped';
                            $status_text = 'Dikirim';
                            break;
                        case 'delivered':
                            $status_class = 'status-delivered';
                            $status_text = 'Selesai';
                            break;
                        case 'cancelled':
                            $status_class = 'status-cancelled';
                            $status_text = 'Dibatalkan';
                            break;
                    }
                    ?>
                    <span class="order-status <?php echo $status_class; ?>">
                        <?php echo $status_text; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informasi Pesanan</h6>
                        <p class="mb-1"><strong>Tanggal Pesanan:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        <p class="mb-1"><strong>Total Pembayaran:</strong> 
                            <span class="text-primary fw-bold"><?php echo format_rupiah($order['total_amount']); ?></span>
                        </p>
                        <p class="mb-1"><strong>Metode Pembayaran:</strong> 
                            <?php if (isset($order['payment_method']) && $order['payment_method'] === 'cod'): ?>
                                <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>Cash On Delivery (COD)</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Bank Transfer</span>
                            <?php endif; ?>
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
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            foreach ($order_items as $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $subtotal += $item_total;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['image'] ? 'uploads/' . $item['image'] : 'assets/images/no-image.jpg'; ?>" 
                                             class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo format_rupiah($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo format_rupiah($item_total); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Subtotal</th>
                                <th><?php echo format_rupiah($subtotal); ?></th>
                            </tr>
                            <tr>
                                <th colspan="3">Ongkos Kirim</th>
                                <th><?php echo format_rupiah($order['total_amount'] - $subtotal); ?></th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="3">Total</th>
                                <th><?php echo format_rupiah($order['total_amount']); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
                    </a>
                    <?php if ($order['status'] == 'pending'): ?>
                        <button class="btn btn-outline-danger ms-2" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-times"></i> Batalkan Pesanan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Status Pesanan</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item <?php echo in_array($order['status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Pesanan Dibuat</h6>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></small>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Pembayaran Dikonfirmasi</h6>
                            <small class="text-muted">
                                <?php echo $order['status'] != 'pending' ? 'Pembayaran telah dikonfirmasi' : 'Menunggu konfirmasi pembayaran'; ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Pesanan Dikirim</h6>
                            <small class="text-muted">
                                <?php echo $order['status'] == 'shipped' || $order['status'] == 'delivered' ? 'Pesanan sedang dalam perjalanan' : 'Menunggu pengiriman'; ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo $order['status'] == 'delivered' ? 'active' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Pesanan Diterima</h6>
                            <small class="text-muted">
                                <?php echo $order['status'] == 'delivered' ? 'Pesanan telah diterima' : 'Menunggu konfirmasi penerimaan'; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Info -->
        <?php if ($order['status'] == 'pending'): ?>
            <?php if (isset($order['payment_method']) && $order['payment_method'] === 'cod'): ?>
            <!-- COD Payment Info -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave"></i> Cash On Delivery (COD)
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2"><strong>Pembayaran dilakukan saat barang diterima.</strong></p>
                    <ul class="small mb-2">
                        <li>Siapkan uang tunai sebesar <strong><?php echo format_rupiah($order['total_amount']); ?></strong></li>
                        <li>Kurir akan menghubungi Anda sebelum pengantaran</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i> Pesanan sedang menunggu diproses oleh admin.
                    </p>
                </div>
            </div>
            <?php else: ?>
            <!-- Bank Transfer Payment Info -->
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-dark">
                        <i class="fas fa-university"></i> Menunggu Pembayaran
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">Silakan lakukan pembayaran ke rekening berikut:</p>
                    <div class="border p-2 rounded mb-3">
                        <strong>Bank Mandiri</strong><br>
                        <small>1070020338341 - Elwina Situmorang</small>
                    </div>
                    <p class="small text-muted">
                        Setelah transfer, kirim bukti pembayaran via WhatsApp ke +62 813 17975623
                    </p>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Contact Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Butuh Bantuan?</h6>
                <p class="card-text small">Hubungi customer service kami:</p>
                <div class="d-grid gap-2">
                    <a href="https://wa.me/6281317975623" class="btn btn-success btn-sm" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="tel:+6281317975623" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-phone"></i> Telepon
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #dee2e6;
    border: 3px solid #fff;
}

.timeline-item.active .timeline-marker {
    background: #28a745;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}
</style>

<script>
function cancelOrder(orderId) {
    if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal membatalkan pesanan');
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