<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit('Unauthorized');
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    exit('Invalid customer ID');
}

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    exit('Customer not found');
}

// Get customer orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();

// Get order statistics
$stmt = $pdo->prepare("SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_spent,
                        AVG(total_amount) as avg_order_value
                       FROM orders WHERE user_id = ?");
$stmt->execute([$customer_id]);
$stats = $stmt->fetch();
?>

<div class="row">
    <div class="col-md-6">
        <h6>Informasi Customer</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Nama Lengkap:</strong></td>
                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Username:</strong></td>
                <td><?php echo htmlspecialchars($customer['username']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Telepon:</strong></td>
                <td><?php echo $customer['phone'] ? htmlspecialchars($customer['phone']) : '-'; ?></td>
            </tr>
            <tr>
                <td><strong>Alamat:</strong></td>
                <td><?php echo $customer['address'] ? nl2br(htmlspecialchars($customer['address'])) : '-'; ?></td>
            </tr>
            <tr>
                <td><strong>Bergabung:</strong></td>
                <td><?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6>Statistik Pesanan</h6>
        <div class="row">
            <div class="col-6">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $stats['total_orders']; ?></h4>
                        <small>Total Pesanan</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6><?php echo format_rupiah($stats['total_spent']); ?></h6>
                        <small>Total Belanja</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6><?php echo format_rupiah($stats['avg_order_value']); ?></h6>
                    <small>Rata-rata Nilai Pesanan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<h6 class="mt-4">Riwayat Pesanan Terbaru</h6>
<?php if (empty($orders)): ?>
    <p class="text-muted">Customer belum pernah melakukan pesanan</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
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
    
    <?php if (count($orders) == 10): ?>
        <p class="text-muted text-center">Menampilkan 10 pesanan terbaru</p>
    <?php endif; ?>
<?php endif; ?>