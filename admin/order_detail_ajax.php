<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit('Unauthorized');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    exit('Invalid order ID');
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone as user_phone 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    exit('Order not found');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-6">
        <h6>Informasi Pesanan</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>No. Pesanan:</strong></td>
                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal:</strong></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
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
            </tr>
            <tr>
                <td><strong>Total:</strong></td>
                <td class="fw-bold text-primary"><?php echo format_rupiah($order['total_amount']); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6>Informasi Customer</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Nama:</strong></td>
                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Telepon:</strong></td>
                <td><?php echo htmlspecialchars($order['phone']); ?></td>
            </tr>
            <tr>
                <td><strong>Alamat:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></td>
            </tr>
        </table>
    </div>
</div>

<?php if ($order['notes']): ?>
<div class="mb-3">
    <h6>Catatan Pesanan</h6>
    <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
</div>
<?php endif; ?>

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
            <?php 
            $subtotal = 0;
            foreach ($order_items as $item): 
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="<?php echo $item['image'] ? '../uploads/' . $item['image'] : '../assets/images/no-image.jpg'; ?>" 
                             class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php echo htmlspecialchars($item['name']); ?>
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