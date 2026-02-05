<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$stmt->execute();
$stats['products'] = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$stats['orders'] = $stmt->fetch()['count'];

// Total customers
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stmt->execute();
$stats['customers'] = $stmt->fetch()['count'];

// Total revenue
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('processing', 'shipped', 'delivered')");
$stmt->execute();
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent orders
$stmt = $pdo->prepare("SELECT o.*, u.full_name FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->prepare("SELECT * FROM products WHERE stock <= 5 AND status = 'active' ORDER BY stock ASC LIMIT 5");
$stmt->execute();
$low_stock = $stmt->fetchAll();

$page_title = "Dashboard Admin";
include 'includes/header.php';
?>

<!-- Welcome Banner -->
<div class="row mb-4 page-header">
    <div class="col-12">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-crown me-2" style="color: #ffd700;"></i>Selamat Datang, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username']; ?>!</h2>
                    <p class="mb-0">Dashboard Admin Gerai Tano Batak - Kelola toko online Anda dengan mudah</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-store fa-4x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="stats-icon mb-3">
                    <i class="fas fa-box fa-3x text-primary"></i>
                </div>
                <h3 class="text-primary"><?php echo $stats['products']; ?></h3>
                <p class="text-muted mb-3">Total Produk</p>
                <a href="products.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Detail
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="stats-icon mb-3">
                    <i class="fas fa-shopping-cart fa-3x text-success"></i>
                </div>
                <h3 class="text-success"><?php echo $stats['orders']; ?></h3>
                <p class="text-muted mb-3">Total Pesanan</p>
                <a href="orders.php" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Detail
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="stats-icon mb-3">
                    <i class="fas fa-users fa-3x text-info"></i>
                </div>
                <h3 class="text-info"><?php echo $stats['customers']; ?></h3>
                <p class="text-muted mb-3">Total Customer</p>
                <a href="customers.php" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Detail
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="stats-icon mb-3">
                    <i class="fas fa-chart-line fa-3x text-warning"></i>
                </div>
                <h3 class="text-warning"><?php echo format_rupiah($stats['revenue']); ?></h3>
                <p class="text-muted mb-3">Total Pendapatan</p>
                <a href="reports.php" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Detail
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pesanan Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-muted">Belum ada pesanan</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo format_rupiah($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($order['status']) {
                                            case 'pending': $status_class = 'bg-warning'; break;
                                            case 'processing': $status_class = 'bg-info'; break;
                                            case 'shipped': $status_class = 'bg-primary'; break;
                                            case 'delivered': $status_class = 'bg-success'; break;
                                            case 'cancelled': $status_class = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="orders.php" class="btn btn-primary">Lihat Semua Pesanan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock)): ?>
                    <p class="text-muted">Semua produk memiliki stok yang cukup</p>
                <?php else: ?>
                    <?php foreach ($low_stock as $product): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <small class="text-muted">Stok: <?php echo $product['stock']; ?> unit</small>
                        </div>
                        <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                    </div>
                    <hr>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="products.php" class="btn btn-outline-danger btn-sm">Kelola Stok</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="products.php?action=add" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="orders.php" class="btn btn-success w-100">
                            <i class="fas fa-list"></i> Kelola Pesanan
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="categories.php" class="btn btn-info w-100">
                            <i class="fas fa-tags"></i> Kelola Kategori
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="reports.php" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar"></i> Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>