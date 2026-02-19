-- ============================================
-- Migrasi: Tambah kolom payment_method ke tabel orders
-- Jalankan query ini di phpMyAdmin atau MySQL CLI
-- ============================================

ALTER TABLE `orders` 
ADD COLUMN `payment_method` ENUM('bank_transfer', 'cod') NOT NULL DEFAULT 'bank_transfer' AFTER `notes`;

-- Update data lama agar menggunakan bank_transfer sebagai default
UPDATE `orders` SET `payment_method` = 'bank_transfer' WHERE `payment_method` = 'bank_transfer';
