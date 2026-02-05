<?php
require_once 'includes/config.php';

// Untuk browser
if (isset($_SERVER['HTTP_HOST'])) {
    echo "<h2>Setup Tabel Reviews</h2>";
    echo "<pre>";
}

echo "=== SETUP TABEL REVIEWS ===\n\n";

try {
    // 1. Buat tabel product_reviews
    echo "1. Membuat tabel product_reviews...\n";
    $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_product_id (product_id),
        INDEX idx_user_id (user_id),
        UNIQUE KEY unique_user_product (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✓ Tabel product_reviews berhasil dibuat\n\n";
    
    // 2. Cek apakah sudah ada data
    echo "2. Mengecek data yang ada...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM product_reviews");
    $count = $stmt->fetch()['count'];
    echo "   Jumlah review saat ini: $count\n\n";
    
    // 3. Tambahkan sample data jika kosong
    if ($count == 0) {
        echo "3. Menambahkan sample data...\n";
        
        // Cek produk yang ada
        $stmt = $pdo->query("SELECT id, name FROM products WHERE status = 'active' LIMIT 3");
        $products = $stmt->fetchAll();
        echo "   Produk tersedia: " . count($products) . "\n";
        
        // Cek user yang ada
        $stmt = $pdo->query("SELECT id, full_name FROM users WHERE role = 'customer' LIMIT 3");
        $users = $stmt->fetchAll();
        echo "   Customer tersedia: " . count($users) . "\n";
        
        // Buat customer jika belum ada
        if (empty($users)) {
            echo "   Membuat sample customer...\n";
            $password = password_hash('customer123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['customer1', 'customer1@test.com', $password, 'Customer Satu', 'customer']);
            $user1_id = $pdo->lastInsertId();
            
            $stmt->execute(['customer2', 'customer2@test.com', $password, 'Customer Dua', 'customer']);
            $user2_id = $pdo->lastInsertId();
            
            $users = [
                ['id' => $user1_id, 'full_name' => 'Customer Satu'],
                ['id' => $user2_id, 'full_name' => 'Customer Dua']
            ];
            echo "   ✓ Sample customer berhasil dibuat\n";
        }
        
        // Tambahkan sample reviews
        if (!empty($products) && !empty($users)) {
            $reviews = [
                [$products[0]['id'], $users[0]['id'], 5, 'Produk sangat bagus! Kualitas ulos nya premium dan motifnya indah sekali.'],
                [$products[0]['id'], $users[1]['id'] ?? $users[0]['id'], 4, 'Bagus, sesuai dengan deskripsi. Pengiriman juga cepat.']
            ];
            
            if (count($products) > 1) {
                $reviews[] = [$products[1]['id'], $users[0]['id'], 5, 'Ulos tradisional yang berkualitas tinggi. Recommended!'];
                if (isset($users[1])) {
                    $reviews[] = [$products[1]['id'], $users[1]['id'], 4, 'Produk bagus, kualitas memuaskan.'];
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            
            foreach ($reviews as $review) {
                try {
                    $stmt->execute($review);
                    echo "   ✓ Review ditambahkan untuk produk ID {$review[0]} (rating: {$review[2]} bintang)\n";
                } catch (Exception $e) {
                    echo "   - Skip review untuk produk ID {$review[0]} (sudah ada)\n";
                }
            }
        }
    } else {
        echo "3. Data review sudah ada, skip sample data\n";
    }
    
    // 4. Tampilkan statistik akhir
    echo "\n4. Statistik akhir:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_reviews");
    $total = $stmt->fetch()['total'];
    echo "   Total reviews: $total\n";
    
    $stmt = $pdo->query("SELECT AVG(rating) as avg_rating FROM product_reviews");
    $avg = $stmt->fetch()['avg_rating'];
    echo "   Rating rata-rata: " . number_format($avg, 1) . "\n";
    
    echo "\n=== SETUP SELESAI ===\n";
    echo "Tabel product_reviews siap digunakan!\n";
    echo "Silakan buka halaman produk untuk melihat fitur review.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Untuk browser
if (isset($_SERVER['HTTP_HOST'])) {
    echo "</pre>";
    echo "<p><a href='products.php'>→ Lihat Produk dengan Review</a></p>";
    echo "<p><a href='admin/products.php'>→ Admin Products</a></p>";
}
?>