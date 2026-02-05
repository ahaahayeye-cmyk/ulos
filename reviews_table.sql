-- Script SQL untuk membuat tabel product_reviews

CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data (opsional)
-- Ganti product_id dan user_id sesuai dengan data yang ada di database Anda

INSERT IGNORE INTO product_reviews (product_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Produk sangat bagus! Kualitas ulos nya premium dan motifnya indah sekali. Sangat puas dengan pembelian ini.'),
(1, 2, 4, 'Bagus, sesuai dengan deskripsi. Pengiriman juga cepat. Hanya saja warnanya sedikit berbeda dari foto.'),
(2, 1, 5, 'Ulos tradisional yang berkualitas tinggi. Motifnya sangat detail dan bahan terasa premium. Recommended!'),
(2, 3, 4, 'Produk bagus, hanya saja warnanya sedikit berbeda dari foto. Tapi kualitas tetap memuaskan.'),
(3, 2, 3, 'Produk cukup bagus, tapi ada beberapa benang yang sedikit longgar. Secara keseluruhan masih OK.');