<?php
require_once 'includes/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Anda harus login untuk memberikan review';
    } else {
        $rating = (int)$_POST['rating'];
        $comment = clean_input($_POST['comment']);
        $user_id = $_SESSION['user_id'];
        
        // Debug: log the rating value
        error_log("Rating received: " . $_POST['rating'] . " (converted to int: " . $rating . ")");
        
        if ($rating < 1 || $rating > 5) {
            $error = 'Rating harus antara 1-5 bintang';
        } elseif (empty($comment)) {
            $error = 'Komentar tidak boleh kosong';
        } else {
            try {
                // Cek apakah user sudah pernah review produk ini
                $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                $existing_review = $stmt->fetch();
                
                if ($existing_review) {
                    // Update review yang sudah ada
                    $stmt = $pdo->prepare("UPDATE product_reviews SET rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$rating, $comment, $user_id, $product_id]);
                    $success = 'Review Anda berhasil diperbarui';
                } else {
                    // Insert review baru
                    $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$product_id, $user_id, $rating, $comment]);
                    $success = 'Terima kasih atas review Anda';
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

// Get product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Get product images
$product_images = get_product_images($pdo, $product_id);
$primary_image = get_product_primary_image($pdo, $product_id);

// Get product rating statistics
$rating_stats = get_product_rating_stats($pdo, $product_id);

// Get product reviews
$stmt = $pdo->prepare("
    SELECT pr.*, u.full_name, u.username 
    FROM product_reviews pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.product_id = ? 
    ORDER BY pr.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// Check if current user has reviewed this product
$user_review = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $user_review = $stmt->fetch();
}

// Get related products
$stmt = $pdo->prepare("SELECT * FROM products 
                       WHERE category_id = ? AND id != ? AND status = 'active' 
                       ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

$page_title = $product['name'];
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
        <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>">
            <?php echo htmlspecialchars($product['category_name']); ?>
        </a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Product Image Gallery -->
    <div class="col-md-6 mb-4">
        <div class="product-gallery">
            <?php if (!empty($product_images)): ?>
                <!-- Main Image -->
                <img id="mainImage" 
                     src="uploads/<?php echo $product_images[0]['image_path']; ?>" 
                     class="main-image" 
                     alt="<?php echo htmlspecialchars($product_images[0]['alt_text'] ?: $product['name']); ?>"
                     onclick="openImageModal(0)"
                     onerror="this.src='assets/images/no-image.svg'">
                
                <!-- Gallery Badge -->
                <?php if (count($product_images) > 1): ?>
                    <div class="gallery-badge">
                        <i class="fas fa-images"></i>
                        <?php echo count($product_images); ?> foto
                    </div>
                <?php endif; ?>
                
                <!-- Thumbnails -->
                <?php if (count($product_images) > 1): ?>
                    <div class="thumbnail-container">
                        <?php foreach ($product_images as $index => $image): ?>
                            <img src="uploads/<?php echo $image['image_path']; ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 alt="<?php echo htmlspecialchars($image['alt_text'] ?: $product['name']); ?>"
                                 onclick="changeMainImage('uploads/<?php echo $image['image_path']; ?>', <?php echo $index; ?>)"
                                 onerror="this.src='assets/images/no-image.svg'">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Fallback to old image field -->
                <img src="<?php echo $product['image'] ? 'uploads/' . $product['image'] : 'assets/images/no-image.svg'; ?>" 
                     class="main-image" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     onerror="this.src='assets/images/no-image.svg'">
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <div class="mb-3">
                    <span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    
                    <?php if ($rating_stats['total_reviews'] > 0): ?>
                        <div class="mt-2">
                            <?php echo display_rating($rating_stats['average_rating']); ?>
                            <small class="text-muted ms-2">
                                (<?php echo $rating_stats['total_reviews']; ?> review<?php echo $rating_stats['total_reviews'] > 1 ? 's' : ''; ?>)
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <h3 class="product-price text-primary"><?php echo format_rupiah($product['price']); ?></h3>
                </div>
                
                <div class="mb-4">
                    <h5>Deskripsi Produk</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="mb-4">
                    <div class="row">
                        <div class="col-6">
                            <strong>Stok Tersedia:</strong>
                            <span class="<?php echo $product['stock'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $product['stock']; ?> unit
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Status:</strong>
                            <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $product['stock'] > 0 ? 'Tersedia' : 'Stok Habis'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($product['stock'] > 0): ?>
                        <div class="mb-4">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <label for="quantity" class="form-label">Jumlah:</label>
                                    <input type="number" class="form-control quantity-input" id="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <div class="col-8">
                                    <button onclick="addToCartWithQuantity()" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button onclick="buyNow()" class="btn btn-success btn-lg">
                                <i class="fas fa-shopping-bag"></i> Beli Sekarang
                            </button>
                            
                            <!-- Tombol Hubungi Penjual -->
                            <?php 
                            $current_url = SITE_URL . $_SERVER['REQUEST_URI'];
                            $whatsapp_link = get_whatsapp_link($product['name'], $product['price'], $current_url);
                            ?>
                            <a href="<?php echo $whatsapp_link; ?>" 
                               target="_blank" 
                               class="btn btn-outline-success btn-lg">
                                <i class="fab fa-whatsapp"></i> Hubungi Penjual
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Maaf, produk ini sedang tidak tersedia
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">
                            Login
                        </a> untuk melakukan pembelian
                    </div>
                    
                    <!-- Tombol Hubungi Penjual untuk user yang belum login -->
                    <div class="d-grid">
                        <?php 
                        $current_url = SITE_URL . $_SERVER['REQUEST_URI'];
                        $whatsapp_link = get_whatsapp_link($product['name'], $product['price'], $current_url);
                        ?>
                        <a href="<?php echo $whatsapp_link; ?>" 
                           target="_blank" 
                           class="btn btn-outline-success btn-lg">
                            <i class="fab fa-whatsapp"></i> Hubungi Penjual
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Features -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Keunggulan Produk</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Bahan berkualitas tinggi</li>
                    <li><i class="fas fa-check text-success me-2"></i> Motif tradisional asli</li>
                    <li><i class="fas fa-check text-success me-2"></i> Proses pembuatan handmade</li>
                    <li><i class="fas fa-check text-success me-2"></i> Tahan lama dan awet</li>
                    <li><i class="fas fa-check text-success me-2"></i> Cocok untuk berbagai acara</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Reviews Section -->
<section class="mt-5">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Review Produk</h3>
            
            <?php if ($rating_stats['total_reviews'] > 0): ?>
                <!-- Rating Summary -->
                <div class="rating-summary">
                    <div class="rating-overview">
                        <div class="text-center">
                            <div class="average-rating"><?php echo number_format($rating_stats['average_rating'], 1); ?></div>
                            <?php echo display_rating($rating_stats['average_rating'], false); ?>
                            <div class="mt-2">
                                <small class="text-muted"><?php echo $rating_stats['total_reviews']; ?> review<?php echo $rating_stats['total_reviews'] > 1 ? 's' : ''; ?></small>
                            </div>
                        </div>
                        
                        <div class="rating-details">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <?php 
                                $count = $rating_stats['rating_' . $i];
                                $percentage = $rating_stats['total_reviews'] > 0 ? ($count / $rating_stats['total_reviews']) * 100 : 0;
                                ?>
                                <div class="rating-bar">
                                    <span class="me-2"><?php echo $i; ?> <i class="fas fa-star text-warning"></i></span>
                                    <div class="rating-bar-fill">
                                        <div class="rating-bar-progress" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="ms-2 text-muted"><?php echo $count; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Review Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <?php echo $user_review ? 'Edit Review Anda' : 'Tulis Review'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Rating *</label>
                                <div class="rating-input">
                                    <input type="radio" name="rating" value="1" id="star1" <?php echo ($user_review && $user_review['rating'] == 1) ? 'checked' : ''; ?> required>
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="2" id="star2" <?php echo ($user_review && $user_review['rating'] == 2) ? 'checked' : ''; ?>>
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="3" id="star3" <?php echo ($user_review && $user_review['rating'] == 3) ? 'checked' : ''; ?>>
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="4" id="star4" <?php echo ($user_review && $user_review['rating'] == 4) ? 'checked' : ''; ?>>
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="5" id="star5" <?php echo ($user_review && $user_review['rating'] == 5) ? 'checked' : ''; ?>>
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="comment" class="form-label">Komentar *</label>
                                <textarea class="form-control" name="comment" id="comment" rows="4" 
                                          placeholder="Bagikan pengalaman Anda dengan produk ini..." required><?php echo $user_review ? htmlspecialchars($user_review['comment']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> 
                                <?php echo $user_review ? 'Update Review' : 'Kirim Review'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">
                        Login
                    </a> untuk memberikan review
                </div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <?php if (!empty($reviews)): ?>
                <h5 class="mb-3">Semua Review (<?php echo count($reviews); ?>)</h5>
                
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <div class="reviewer-name"><?php echo htmlspecialchars($review['full_name']); ?></div>
                                <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="review-rating">
                                <?php echo display_rating($review['rating'], false); ?>
                            </div>
                        </div>
                        
                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>
                        
                        <?php if ($review['updated_at'] != $review['created_at']): ?>
                            <small class="text-muted">
                                <i class="fas fa-edit"></i> Diperbarui <?php echo date('d M Y', strtotime($review['updated_at'])); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada review</h5>
                    <p class="text-muted">Jadilah yang pertama memberikan review untuk produk ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="mt-5">
    <h3 class="mb-4">Produk Terkait</h3>
    <div class="row">
        <?php foreach($related_products as $related): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card product-card h-100">
                <img src="<?php echo $related['image'] ? 'uploads/' . $related['image'] : 'assets/images/no-image.svg'; ?>" 
                     class="card-img-top product-image" 
                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                     onerror="this.src='assets/images/no-image.svg'">
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                    <p class="card-text text-muted small flex-grow-1">
                        <?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="product-price"><?php echo format_rupiah($related['price']); ?></span>
                        <small class="text-muted">Stok: <?php echo $related['stock']; ?></small>
                    </div>
                    <div class="mt-auto">
                        <div class="d-grid gap-2">
                            <a href="product_detail.php?id=<?php echo $related['id']; ?>" 
                               class="btn btn-outline-primary btn-sm">
                                Lihat Detail
                            </a>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <?php if($related['stock'] > 0): ?>
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <button onclick="addToCart(<?php echo $related['id']; ?>)" 
                                                    class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-cart-plus"></i> Keranjang
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button onclick="buyNowFromList(<?php echo $related['id']; ?>)" 
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
                            
                            <!-- Tombol Hubungi Penjual untuk produk terkait -->
                            <?php 
                            $related_product_url = SITE_URL . '/product_detail.php?id=' . $related['id'];
                            $related_whatsapp_link = get_whatsapp_link($related['name'], $related['price'], $related_product_url);
                            ?>
                            <a href="<?php echo $related_whatsapp_link; ?>" 
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
</section>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="modal-close" onclick="closeImageModal()">&times;</span>
    <img id="modalImage" class="modal-content-image" alt="Product Image">
    
    <?php if (count($product_images) > 1): ?>
        <button class="modal-nav prev" onclick="prevImage()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="modal-nav next" onclick="nextImage()">
            <i class="fas fa-chevron-right"></i>
        </button>
    <?php endif; ?>
</div>

<script>
// Product Images Gallery
const productImages = <?php echo json_encode($product_images); ?>;
let currentImageIndex = 0;

function changeMainImage(imageSrc, index) {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    // Update main image
    mainImage.src = imageSrc;
    currentImageIndex = index;
    
    // Update active thumbnail
    thumbnails.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
}

function openImageModal(index) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    currentImageIndex = index;
    modalImage.src = 'uploads/' + productImages[index].image_path;
    modalImage.alt = productImages[index].alt_text || '<?php echo htmlspecialchars($product['name']); ?>';
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

function nextImage() {
    if (currentImageIndex < productImages.length - 1) {
        currentImageIndex++;
    } else {
        currentImageIndex = 0;
    }
    
    const modalImage = document.getElementById('modalImage');
    modalImage.src = 'uploads/' + productImages[currentImageIndex].image_path;
    modalImage.alt = productImages[currentImageIndex].alt_text || '<?php echo htmlspecialchars($product['name']); ?>';
}

function prevImage() {
    if (currentImageIndex > 0) {
        currentImageIndex--;
    } else {
        currentImageIndex = productImages.length - 1;
    }
    
    const modalImage = document.getElementById('modalImage');
    modalImage.src = 'uploads/' + productImages[currentImageIndex].image_path;
    modalImage.alt = productImages[currentImageIndex].alt_text || '<?php echo htmlspecialchars($product['name']); ?>';
}

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    } else if (e.key === 'ArrowRight') {
        nextImage();
    } else if (e.key === 'ArrowLeft') {
        prevImage();
    }
});

// Close modal when clicking outside image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Rating input functionality
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    // Handle rating hover effect
    ratingLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            // Get the rating value from the corresponding input
            const ratingValue = parseInt(ratingInputs[index].value);
            
            // Highlight stars up to hovered rating
            ratingLabels.forEach((lbl, i) => {
                const starValue = parseInt(ratingInputs[i].value);
                if (starValue <= ratingValue) {
                    lbl.style.color = '#ffc107';
                } else {
                    lbl.style.color = '#ddd';
                }
            });
        });
    });
    
    // Reset to selected rating on mouse leave
    document.querySelector('.rating-input').addEventListener('mouseleave', function() {
        updateRatingDisplay();
    });
    
    // Handle rating selection
    ratingInputs.forEach((input) => {
        input.addEventListener('change', function() {
            updateRatingDisplay();
        });
    });
    
    // Function to update rating display
    function updateRatingDisplay() {
        const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
        
        if (checkedInput) {
            const selectedRating = parseInt(checkedInput.value);
            
            // Highlight stars up to selected rating
            ratingLabels.forEach((label, i) => {
                const starValue = parseInt(ratingInputs[i].value);
                if (starValue <= selectedRating) {
                    label.style.color = '#ffc107';
                } else {
                    label.style.color = '#ddd';
                }
            });
        } else {
            // Reset all if none selected
            ratingLabels.forEach(label => {
                label.style.color = '#ddd';
            });
        }
    }
    
    // Initialize rating display
    updateRatingDisplay();
});

function addToCartWithQuantity() {
    const quantity = document.getElementById('quantity').value;
    addToCart(<?php echo $product_id; ?>, quantity);
}

function buyNow() {
    const quantity = document.getElementById('quantity').value;
    
    fetch('buy_now.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=<?php echo $product_id; ?>&quantity=${quantity}`
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

// Update quantity validation
document.getElementById('quantity').addEventListener('change', function() {
    const max = parseInt(this.getAttribute('max'));
    const min = parseInt(this.getAttribute('min'));
    let value = parseInt(this.value);
    
    if (value > max) {
        this.value = max;
        alert(`Maksimal pembelian ${max} unit`);
    } else if (value < min) {
        this.value = min;
    }
});

// Function untuk beli sekarang dari produk terkait
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