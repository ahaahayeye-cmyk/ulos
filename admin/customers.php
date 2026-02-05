<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get customers with order statistics
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

$where_conditions = ["u.role = 'customer'"];
$params = [];

if ($search) {
    $where_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where_conditions);

$sql = "SELECT u.*, 
               COUNT(o.id) as total_orders,
               COALESCE(SUM(o.total_amount), 0) as total_spent,
               MAX(o.created_at) as last_order_date
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        WHERE $where_clause 
        GROUP BY u.id 
        ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$page_title = "Data Customer";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header">
    <h2>Data Customer</h2>
    <div class="badge bg-primary fs-6"><?php echo count($customers); ?> customer</div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" placeholder="Cari customer..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Cari</button>
            </div>
            <div class="col-md-2">
                <a href="customers.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Kontak</th>
                        <th>Total Pesanan</th>
                        <th>Total Belanja</th>
                        <th>Pesanan Terakhir</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $customer): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($customer['full_name']); ?></strong>
                                <br>
                                <small class="text-muted">@<?php echo htmlspecialchars($customer['username']); ?></small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customer['email']); ?></small>
                                <br>
                                <?php if ($customer['phone']): ?>
                                    <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['phone']); ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $customer['total_orders']; ?> pesanan</span>
                        </td>
                        <td class="fw-bold text-success">
                            <?php echo format_rupiah($customer['total_spent']); ?>
                        </td>
                        <td>
                            <?php if ($customer['last_order_date']): ?>
                                <?php echo date('d/m/Y', strtotime($customer['last_order_date'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Belum pernah</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customer Detail Modal -->
<div class="modal fade" id="customerDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewCustomer(customerId) {
    fetch(`customer_detail_ajax.php?id=${customerId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('customerDetailContent').innerHTML = data;
            new bootstrap.Modal(document.getElementById('customerDetailModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail customer');
        });
}
</script>

<?php include 'includes/footer.php'; ?>