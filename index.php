<?php
require_once 'includes/config.php';

// Ambil produk terbaru
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.status = 'active' 
                       ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute();
$latest_products = $stmt->fetchAll();

// Ambil kategori
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

$page_title = "Beranda";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-text">
                        <h1 class="hero-title">
                            <span class="text-gradient">Warisan Budaya</span><br>
                            <span class="text-elegant">Ulos Tradisional</span>
                        </h1>
                        <p class="hero-subtitle">
                            Temukan keindahan ulos autentik dengan motif tradisional Batak yang memukau. 
                            Setiap helai menceritakan kisah budaya yang tak ternilai.
                        </p>
                        <div class="hero-buttons">
                            <a href="products.php" class="btn btn-elegant btn-lg me-3">
                                <i class="fas fa-shopping-bag me-2"></i>Jelajahi Koleksi
                            </a>
                            <a href="#about" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-info-circle me-2"></i>Pelajari Lebih
                            </a>
                        </div>
                        <div class="hero-stats mt-5">
                            <div class="row">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="stat-number">500+</h3>
                                        <p class="stat-label">Produk Ulos</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="stat-number">1000+</h3>
                                        <p class="stat-label">Pelanggan Puas</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="stat-number">50+</h3>
                                        <p class="stat-label">Motif Tradisional</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="floating-card">
                            <img src="assets/images/hero-ulos.jpg" alt="Ulos Tradisional" class="img-fluid rounded-3 shadow-lg">
                            <div class="floating-badge">
                                <i class="fas fa-star text-warning"></i>
                                <span>Premium Quality</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll">
        <a href="#categories" class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</div>

<!-- Kategori Section -->
<section id="categories" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Kategori <span class="text-gradient">Ulos</span></h2>
            <p class="section-subtitle">Jelajahi berbagai jenis ulos dengan motif dan kegunaan yang berbeda</p>
        </div>
        <div class="row">
            <?php foreach($categories as $category): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="category-card-elegant">
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-link">
                        <div class="category-icon-elegant">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="category-content">
                            <h5 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                            <div class="btn btn-category">
                                <span>Jelajahi</span>
                                <i class="fas fa-arrow-right ms-2"></i>
                            </div>
                        </div>
                    </a>
                    <div class="category-overlay"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produk Terbaru Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Koleksi <span class="text-gradient">Terbaru</span></h2>
            <p class="section-subtitle">Temukan ulos terbaru dengan desain dan motif yang memukau</p>
        </div>
        <div class="row">
            <?php foreach($latest_products as $product): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card product-card">
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-image-link">
                        <img src="<?php echo $product['image'] ? 'uploads/' . $product['image'] : 'assets/images/no-image.svg'; ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='assets/images/no-image.svg'">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price"><?php echo format_rupiah($product['price']); ?></span>
                            <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                        </div>
                        <div class="mt-3">
                            <div class="d-grid gap-2">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">Detail</a>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <?php if($product['stock'] > 0): ?>
                                        <div class="row g-1 mb-2">
                                            <div class="col-6">
                                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="fas fa-cart-plus"></i> Keranjang
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button onclick="buyNowFromList(<?php echo $product['id']; ?>)" class="btn btn-success btn-sm w-100">
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
                                    <a href="login.php" class="btn btn-primary btn-sm mb-2">Login untuk Beli</a>
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
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-primary btn-lg">Lihat Semua Produk</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5 bg-gradient-elegant">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <div class="about-content">
                    <h2 class="section-title text-white">Tentang <span class="text-gold">Ulos Tradisional</span></h2>
                    <p class="text-white-75 mb-4">
                        Ulos adalah kain tradisional Batak yang memiliki makna mendalam dalam budaya. 
                        Setiap motif dan warna memiliki filosofi tersendiri yang mencerminkan nilai-nilai 
                        kehidupan masyarakat Batak.
                    </p>
                    <div class="about-features">
                        <div class="feature-item mb-3">
                            <i class="fas fa-check-circle text-gold me-3"></i>
                            <span class="text-white">Dibuat dengan teknik tenun tradisional</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-check-circle text-gold me-3"></i>
                            <span class="text-white">Menggunakan bahan berkualitas premium</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-check-circle text-gold me-3"></i>
                            <span class="text-white">Motif autentik dengan makna filosofis</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="assets/images/about-ulos.jpg" alt="Proses Pembuatan Ulos" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Keunggulan Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Mengapa Memilih <span class="text-gradient">Kami?</span></h2>
            <p class="section-subtitle">Komitmen kami untuk memberikan yang terbaik bagi Anda</p>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="feature-card-elegant">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="feature-content">
                        <h5 class="feature-title">Kualitas Terjamin</h5>
                        <p class="feature-description">Semua ulos kami dibuat dengan bahan berkualitas tinggi dan proses pembuatan yang teliti oleh pengrajin berpengalaman.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="feature-card-elegant">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="feature-content">
                        <h5 class="feature-title">Pengiriman Cepat</h5>
                        <p class="feature-description">Kami menjamin pengiriman yang cepat dan aman ke seluruh Indonesia dengan packaging yang rapi dan aman.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="feature-card-elegant">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h5 class="feature-title">Layanan 24/7</h5>
                        <p class="feature-description">Tim customer service kami siap membantu Anda kapan saja dengan respon yang cepat dan solusi terbaik.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Counter animation for stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace('+', ''));
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target + '+';
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current) + '+';
            }
        }, 20);
    });
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            
            // Animate counters when hero stats come into view
            if (entry.target.classList.contains('hero-stats')) {
                animateCounters();
            }
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.category-card-elegant, .feature-card-elegant, .product-card');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Observe hero stats
    const heroStats = document.querySelector('.hero-stats');
    if (heroStats) {
        observer.observe(heroStats);
    }
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const heroContent = document.querySelector('.hero-content');
    
    if (heroContent) {
        heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// Function untuk beli sekarang dari halaman beranda
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