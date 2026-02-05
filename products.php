<?php
require_once 'includes/config.php';

// Parameter pencarian dan filter
// Get products with rating
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Sorting
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_clause .= "p.price ASC";
        break;
    case 'price_high':
        $order_clause .= "p.price DESC";
        break;
    case 'name':
        $order_clause .= "p.name ASC";
        break;
    default:
        $order_clause .= "p.created_at DESC";
}

// Count total products
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
// Query untuk mendapatkan produk dengan rating dan primary image
$sql = "SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating,
               COUNT(pr.id) as review_count,
               pi.image_path as primary_image
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'active'";

if (!empty($where_conditions)) {
    $sql .= " AND " . $where_clause;
}

$sql .= " GROUP BY p.id";

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC, review_count DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY review_count DESC, avg_rating DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    default: // newest
        $sql .= " ORDER BY p.created_at DESC";
}

$sql .= " LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$cat_stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();

$page_title = "Produk";
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item active">Produk</li>
    </ol>
</nav>

<div class="row">
    <!-- Sidebar Filter -->
    <div class="col-lg-3 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Produk</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <!-- Search -->
                    <div class="mb-3">
                        <label for="search" class="form-label">Cari Produk</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nama produk...">
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Sort -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Urutkan</label>
                        <select class="form-select" id="sort" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                        <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Paling Populer</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                    <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Products -->
    <div class="col-lg-9">
        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Produk Ulos</h4>
                <p class="text-muted">Menampilkan <?php echo count($products); ?> dari <?php echo $total_products; ?> produk</p>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-2">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>Tidak ada produk ditemukan</h5>
                <p class="text-muted">Coba ubah filter pencarian Anda</p>
                <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="row">
                <?php foreach($products as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card product-card h-100">
                            <?php 
                            $display_image = $product['primary_image'] ?: $product['image'];
                            $image_src = $display_image ? 'uploads/' . $display_image : 'assets/images/no-image.svg';
                            ?>
                            <img src="<?php echo $image_src; ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='assets/images/no-image.svg'">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                </small>
                            </div>
                        <div class="mb-2">
                            <?php if ($product['review_count'] > 0): ?>
                                <div class="mb-2">
                                    <?php echo display_rating($product['avg_rating'], false); ?>
                                    <small class="text-muted ms-1">
                                        (<?php echo $product['review_count']; ?> review<?php echo $product['review_count'] > 1 ? 's' : ''; ?>)
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="product-price"><?php echo format_rupiah($product['price']); ?></span>
                            <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                        </div>
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <?php if($product['stock'] > 0): ?>
                                            <div class="row g-1 mb-2">
                                                <div class="col-6">
                                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                                            class="btn btn-outline-primary btn-sm w-100">
                                                        <i class="fas fa-cart-plus"></i> Keranjang
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button onclick="buyNowFromList(<?php echo $product['id']; ?>)" 
                                                            class="btn btn-success btn-sm w-100">
                                                        <i class="fas fa-shopping-bag"></i> Beli Sekarang
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm mb-2" disabled>
                                                <i class="fas fa-times"></i> Stok Habis
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary btn-sm mb-2">
                                            <i class="fas fa-sign-in-alt"></i> Login untuk Beli
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Hubungi Penjual -->
                                    <?php 
                                    $product_url = SITE_URL . '/product_detail.php?id=' . $product['id'];
                                    $whatsapp_link = get_whatsapp_link($product['name'], $product['price'], $product_url);
                                    ?>
                                    <a href="<?php echo $whatsapp_link; ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        <i class="fab fa-whatsapp"></i> Hubungi Penjual
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Function untuk beli sekarang dari halaman produk
function buyNowFromList(productId) {
    fetch('buy_now.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'checkout.php?mode=buy_now';
        } else {
            alert(data.message || 'Gagal memproses pembelian');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>

<?php include 'includes/footer.php'; ?>