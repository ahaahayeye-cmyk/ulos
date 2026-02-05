<?php
// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ulos_ecommerce');

// Konfigurasi Aplikasi
define('SITE_URL', 'http://localhost/ulos');
define('SITE_NAME', 'Gerai Tano Batak');
define('ADMIN_EMAIL', 'admin@ulos.com');
define('WHATSAPP_NUMBER', '6281317975623'); // Nomor WhatsApp tanpa tanda +

// Koneksi Database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Test koneksi
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Koneksi database gagal: " . $e->getMessage() . "<br>File: " . __FILE__ . "<br>Line: " . __LINE__);
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk format rupiah
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi untuk menampilkan rating bintang
function display_rating($rating, $show_number = true) {
    $output = '<div class="rating-stars">';
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '<i class="fas fa-star star filled"></i>';
        } else {
            $output .= '<i class="far fa-star star"></i>';
        }
    }
    
    if ($show_number) {
        $output .= '<span class="ms-2">(' . number_format($rating, 1) . ')</span>';
    }
    
    $output .= '</div>';
    return $output;
}

// Fungsi untuk mendapatkan statistik rating produk
function get_product_rating_stats($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
        FROM product_reviews 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan gambar produk
function get_product_images($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan gambar utama produk
function get_product_primary_image($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT image_path FROM product_images 
        WHERE product_id = ? AND is_primary = 1 
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['image_path'];
    }
    
    // Fallback ke gambar pertama jika tidak ada primary
    $stmt = $pdo->prepare("
        SELECT image_path FROM product_images 
        WHERE product_id = ? 
        ORDER BY sort_order ASC 
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    
    return $result ? $result['image_path'] : null;
}

// Fungsi untuk menambahkan gambar produk
function add_product_image($pdo, $product_id, $image_path, $is_primary = false, $alt_text = '') {
    // Jika ini primary image, set yang lain jadi non-primary
    if ($is_primary) {
        $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        $stmt->execute([$product_id]);
    }
    
    // Get next sort order
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $sort_order = $stmt->fetch()['next_order'];
    
    // Insert new image
    $stmt = $pdo->prepare("
        INSERT INTO product_images (product_id, image_path, is_primary, sort_order, alt_text) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$product_id, $image_path, $is_primary ? 1 : 0, $sort_order, $alt_text]);
}

// Fungsi untuk generate link WhatsApp
function get_whatsapp_link($product_name, $product_price, $product_url = '') {
    $message = "Halo, saya tertarik dengan produk:\n\n";
    $message .= "*" . $product_name . "*\n";
    $message .= "Harga: " . format_rupiah($product_price) . "\n";
    
    if ($product_url) {
        $message .= "Link: " . $product_url . "\n";
    }
    
    $message .= "\nBisakah Anda memberikan informasi lebih lanjut?";
    
    $encoded_message = urlencode($message);
    return "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . $encoded_message;
}

// Mulai session
session_start();
?>