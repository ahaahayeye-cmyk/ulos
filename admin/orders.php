<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = clean_input($_POST['status']);
    
    if (empty($status) || $order_id <= 0) {
        $error = 'Data tidak valid';
    } else {
        try {
            // Cek apakah order exists
            $check_stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
            $check_stmt->execute([$order_id]);
            $existing_order = $check_stmt->fetch();
            
            if (!$existing_order) {
                $error = 'Pesanan tidak ditemukan';
            } else {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $result = $stmt->execute([$status, $order_id]);
                $affected_rows = $stmt->rowCount();
                
                if ($result && $affected_rows > 0) {
                    $success = 'Status pesanan berhasil diupdate dari "' . $existing_order['status'] . '" ke "' . $status . '"';
                } elseif ($result && $affected_rows == 0) {
                    $success = 'Status pesanan sudah sesuai (tidak ada perubahan)';
                } else {
                    $error = 'Gagal mengupdate status pesanan';
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
            error_log("Orders.php error: " . $e->getMessage());
        }
    }
}

// Get orders with filters
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT o.*, u.full_name, u.email FROM orders o 
        JOIN users u ON o.user_id = u.id 
        $where_clause 
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = "Kelola Pesanan";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header">
    <h2>Kelola Pesanan</h2>
    <div class="badge bg-primary fs-6"><?php echo count($orders); ?> pesanan</div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Cari pesanan, customer..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                            </div>
                        </td>
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
                                    $status_class = 'bg-warning';
                                    $status_text = 'Pending';
                                    break;
                                case 'processing':
                                    $status_class = 'bg-info';
                                    $status_text = 'Processing';
                                    break;
                                case 'shipped':
                                    $status_class = 'bg-primary';
                                    $status_text = 'Shipped';
                                    break;
                                case 'delivered':
                                    $status_class = 'bg-success';
                                    $status_text = 'Delivered';
                                    break;
                                case 'cancelled':
                                    $status_class = 'bg-danger';
                                    $status_text = 'Cancelled';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                <i class="fas fa-edit"></i> Status
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status Pesanan</label>
                        <select class="form-select" name="status" id="status_select" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    fetch(`order_detail_ajax.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetailContent').innerHTML = data;
            new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail pesanan');
        });
}

function updateStatus(orderId, currentStatus) {
    document.getElementById('status_order_id').value = orderId;
    document.getElementById('status_select').value = currentStatus;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>