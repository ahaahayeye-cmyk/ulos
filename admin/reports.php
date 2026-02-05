<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Sales statistics
$stmt = $pdo->prepare("SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        AVG(total_amount) as avg_order_value
                       FROM orders 
                       WHERE status IN ('processing', 'shipped', 'delivered') 
                       AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$sales_stats = $stmt->fetch();

// Top selling products
$stmt = $pdo->prepare("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.id
                       JOIN orders o ON oi.order_id = o.id
                       WHERE o.status IN ('processing', 'shipped', 'delivered')
                       AND DATE(o.created_at) BETWEEN ? AND ?
                       GROUP BY p.id
                       ORDER BY total_sold DESC
                       LIMIT 10");
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll();

// Daily sales
$stmt = $pdo->prepare("SELECT DATE(created_at) as date, 
                              COUNT(*) as orders, 
                              SUM(total_amount) as revenue
                       FROM orders 
                       WHERE status IN ('processing', 'shipped', 'delivered')
                       AND DATE(created_at) BETWEEN ? AND ?
                       GROUP BY DATE(created_at)
                       ORDER BY date DESC");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Order status distribution
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count
                       FROM orders 
                       WHERE DATE(created_at) BETWEEN ? AND ?
                       GROUP BY status");
$stmt->execute([$start_date, $end_date]);
$status_distribution = $stmt->fetchAll();

$page_title = "Laporan";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header">
    <h2>Laporan Penjualan</h2>
    <button class="btn btn-success btn-add-product" onclick="exportReport()">
        <i class="fas fa-download"></i> Export Excel
    </button>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Filter</button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="reports.php" class="btn btn-outline-secondary d-block">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $sales_stats['total_orders']; ?></h4>
                        <p class="mb-0">Total Pesanan</p>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5><?php echo format_rupiah($sales_stats['total_revenue']); ?></h5>
                        <p class="mb-0">Total Pendapatan</p>
                    </div>
                    <div>
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5><?php echo format_rupiah($sales_stats['avg_order_value']); ?></h5>
                        <p class="mb-0">Rata-rata Pesanan</p>
                    </div>
                    <div>
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo count($daily_sales); ?></h4>
                        <p class="mb-0">Hari Aktif</p>
                    </div>
                    <div>
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_products)): ?>
                    <p class="text-muted">Tidak ada data penjualan</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Terjual</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $product['total_sold']; ?></span></td>
                                    <td><?php echo format_rupiah($product['revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Status -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Pesanan</h5>
            </div>
            <div class="card-body">
                <?php if (empty($status_distribution)): ?>
                    <p class="text-muted">Tidak ada data pesanan</p>
                <?php else: ?>
                    <?php foreach ($status_distribution as $status): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?php echo ucfirst($status['status']); ?></span>
                        <span class="badge bg-secondary"><?php echo $status['count']; ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php 
                        $total_orders = array_sum(array_column($status_distribution, 'count'));
                        $percentage = ($status['count'] / $total_orders) * 100;
                        ?>
                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daily Sales -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Penjualan Harian</h5>
    </div>
    <div class="card-body">
        <?php if (empty($daily_sales)): ?>
            <p class="text-muted">Tidak ada data penjualan</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Pesanan</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_sales as $sale): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($sale['date'])); ?></td>
                            <td><span class="badge bg-primary"><?php echo $sale['orders']; ?></span></td>
                            <td class="fw-bold text-success"><?php echo format_rupiah($sale['revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge bg-primary"><?php echo array_sum(array_column($daily_sales, 'orders')); ?></span></th>
                            <th class="fw-bold"><?php echo format_rupiah(array_sum(array_column($daily_sales, 'revenue'))); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function exportReport() {
    const startDate = '<?php echo $start_date; ?>';
    const endDate = '<?php echo $end_date; ?>';
    window.open(`export_report.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>