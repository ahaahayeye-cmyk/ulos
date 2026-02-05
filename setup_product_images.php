<?php
require_once 'includes/config.php';

echo "<h2>Setup Product Images Gallery</h2>";

try {
    // Buat tabel product_images
    $sql = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        alt_text VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id),
        INDEX idx_sort_order (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✅ Tabel product_images berhasil dibuat<br>";
    
    // Cek struktur tabel
    $stmt = $pdo->query("DESCRIBE product_images");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Struktur Tabel product_images:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Migrasi gambar existing dari tabel products ke product_images
    echo "<h3>Migrasi Gambar Existing:</h3>";
    $stmt = $pdo->query("SELECT id, image FROM products WHERE image IS NOT NULL AND image != ''");
    $products_with_images = $stmt->fetchAll();
    
    if ($products_with_images) {
        $insert_stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order, alt_text) VALUES (?, ?, 1, 1, ?)");
        
        foreach ($products_with_images as $product) {
            // Cek apakah sudah ada di product_images
            $check_stmt = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ? AND image_path = ?");
            $check_stmt->execute([$product['id'], $product['image']]);
            
            if (!$check_stmt->fetch()) {
                $alt_text = "Gambar produk ID " . $product['id'];
                $insert_stmt->execute([$product['id'], $product['image'], $alt_text]);
                echo "✅ Migrasi gambar untuk produk ID " . $product['id'] . ": " . $product['image'] . "<br>";
            } else {
                echo "⚠️ Gambar produk ID " . $product['id'] . " sudah ada di gallery<br>";
            }
        }
    } else {
        echo "Tidak ada gambar existing untuk dimigrasi<br>";
    }
    
    // Tambahkan sample images untuk demo
    echo "<h3>Menambahkan Sample Images:</h3>";
    $stmt = $pdo->query("SELECT id FROM products LIMIT 3");
    $sample_products = $stmt->fetchAll();
    
    if ($sample_products) {
        $sample_images = [
            'ulos1.svg', 'ulos2.svg', 'ulos3.svg', 'ulos4.svg', 'ulos5.svg'
        ];
        
        foreach ($sample_products as $index => $product) {
            $product_id = $product['id'];
            
            // Tambahkan 2-3 gambar untuk setiap produk sample
            for ($i = 0; $i < 3; $i++) {
                $image_index = ($index * 3 + $i) % count($sample_images);
                $image_path = $sample_images[$image_index];
                $is_primary = ($i == 0) ? 1 : 0;
                $sort_order = $i + 1;
                $image_number = $i + 1;
                $alt_text = "Gambar " . $image_number . " produk ID " . $product_id;
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order, alt_text) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$product_id, $image_path, $is_primary, $sort_order, $alt_text]);
                    echo "✅ Sample image " . $image_number . " ditambahkan untuk produk ID " . $product_id . "<br>";
                } catch (Exception $e) {
                    echo "⚠️ Skip duplicate image untuk produk ID " . $product_id . "<br>";
                }
            }
        }
    }
    
    // Tampilkan statistik
    echo "<h3>Statistik Product Images:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_images");
    $total_result = $stmt->fetch();
    $total = $total_result['total'];
    echo "Total images: " . $total . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT product_id) as products FROM product_images");
    $products_result = $stmt->fetch();
    $products_count = $products_result['products'];
    echo "Produk dengan images: " . $products_count . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as primary_images FROM product_images WHERE is_primary = 1");
    $primary_result = $stmt->fetch();
    $primary_count = $primary_result['primary_images'];
    echo "Primary images: " . $primary_count . "<br>";
    
    // Tampilkan sample data
    echo "<h3>Sample Product Images:</h3>";
    $stmt = $pdo->query("
        SELECT pi.*, p.name as product_name 
        FROM product_images pi 
        JOIN products p ON pi.product_id = p.id 
        ORDER BY pi.product_id, pi.sort_order 
        LIMIT 10
    ");
    $sample_images = $stmt->fetchAll();
    
    if ($sample_images) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Product</th><th>Image</th><th>Primary</th><th>Sort</th><th>Alt Text</th></tr>";
        foreach ($sample_images as $img) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($img['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($img['image_path']) . "</td>";
            echo "<td>" . ($img['is_primary'] ? '✅' : '❌') . "</td>";
            echo "<td>" . $img['sort_order'] . "</td>";
            echo "<td>" . htmlspecialchars($img['alt_text']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br>✅ Setup product images gallery selesai!<br>";
    echo "<p><strong>Langkah selanjutnya:</strong></p>";
    echo "<ul>";
    echo "<li><a href='admin/products.php'>→ Admin Products (upload multiple images)</a></li>";
    echo "<li><a href='products.php'>→ Lihat Produk dengan Gallery</a></li>";
    echo "<li>Pilih produk untuk melihat gallery images</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Detail: " . $e->getTraceAsString();
}
?>