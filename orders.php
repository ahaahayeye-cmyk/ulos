<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$page_title = "Pesanan Saya";
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item active">Pesanan Saya</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-shopping-bag"></i> Pesanan Saya
            <span class="badge bg-primary ms-2"><?php echo count($orders); ?> pesanan</span>
        </h4>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h5>Belum ada pesanan</h5>
                <p class="text-muted">Anda belum pernah melakukan pemesanan</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="fw-bold text-primary"><?php echo format_rupiah($order['total_amount']); ?></td>
                            <td>
                                <?php if (isset($order['payment_method']) && $order['payment_method'] === 'cod'): ?>
                                    <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>COD</span>
                                <?php else: ?>
                                    <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Transfer</span>
                                <?php endif; ?>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <button class="btn btn-outline-danger btn-sm ms-1" 
                                            onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Status Info -->
<div class="card mt-4">
    <div class="card-body">
        <h6 class="card-title">Keterangan Status Pesanan</h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><span class="order-status status-pending">Menunggu Pembayaran</span> - Pesanan menunggu konfirmasi pembayaran</li>
                    <li><span class="order-status status-processing">Diproses</span> - Pesanan sedang diproses dan dikemas</li>
                    <li><span class="order-status status-shipped">Dikirim</span> - Pesanan sedang dalam perjalanan</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><span class="order-status status-delivered">Selesai</span> - Pesanan telah diterima</li>
                    <li><span class="order-status status-cancelled">Dibatalkan</span> - Pesanan dibatalkan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

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