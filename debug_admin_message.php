<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/kirimi_config.php';

echo "<h2>Debug Pengiriman Pesan ke Admin</h2>";
echo "<hr>";

// Cek konfigurasi
echo "<h3>1. Konfigurasi Kirimi:</h3>";
echo "User Code: " . KIRIMI_USER_CODE . "<br>";
echo "Device ID: " . KIRIMI_DEVICE_ID . "<br>";
echo "Secret Key: " . substr(KIRIMI_SECRET_KEY, 0, 20) . "...<br>";
echo "API URL: " . KIRIMI_API_URL . "<br>";
echo "<hr>";

// Test nomor admin
$admin_phone = '6281317975623';
echo "<h3>2. Nomor Admin:</h3>";
echo "Nomor: $admin_phone<br>";
echo "<hr>";

// Buat pesan test sederhana
$simple_message = "Test pesan ke admin\nWaktu: " . date('d/m/Y H:i:s');

echo "<h3>3. Mengirim Pesan Test Sederhana:</h3>";
echo "<pre>$simple_message</pre>";

$result1 = sendKirimiMessage($admin_phone, $simple_message);

echo "<strong>Hasil:</strong><br>";
echo "Status: " . $result1['status'] . "<br>";
echo "Error: " . ($result1['error'] ?: 'Tidak ada') . "<br>";
echo "Response: <pre>" . print_r($result1['data'], true) . "</pre>";
echo "<hr>";

// Test dengan pesan lengkap seperti di checkout
echo "<h3>4. Mengirim Pesan Lengkap (Simulasi Order):</h3>";

$order_number = "000123";
$payment_text = "Bank Transfer";
$customer_name = "Test Customer";
$customer_phone = "081234567890";
$customer_email = "test@email.com";
$product_list = "• Ulos Ragi Hotang (x1) - Rp 250.000\n";
$subtotal = 250000;
$shipping_cost = 15000;
$total_amount = 265000;
$shipping_address = "Jl. Test No. 123, Jakarta";

$admin_message = "🔔 *PESANAN BARU MASUK!*\n\n";
$admin_message .= "📋 *Detail Pesanan:*\n";
$admin_message .= "• Nomor Pesanan: *#{$order_number}*\n";
$admin_message .= "• Tanggal: " . date('d/m/Y H:i') . "\n\n";
$admin_message .= "👤 *Data Customer:*\n";
$admin_message .= "• Nama: *{$customer_name}*\n";
$admin_message .= "• Telepon: {$customer_phone}\n";
$admin_message .= "• Email: {$customer_email}\n\n";
$admin_message .= "📦 *Produk yang Dipesan:*\n";
$admin_message .= $product_list . "\n";
$admin_message .= "💰 *Total Pembayaran:*\n";
$admin_message .= "• Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n";
$admin_message .= "• Ongkir: Rp " . number_format($shipping_cost, 0, ',', '.') . "\n";
$admin_message .= "• *TOTAL: Rp " . number_format($total_amount, 0, ',', '.') . "*\n\n";
$admin_message .= "💳 *Metode Pembayaran:* {$payment_text}\n\n";
$admin_message .= "📍 *Alamat Pengiriman:*\n";
$admin_message .= $shipping_address . "\n\n";
$admin_message .= "Segera proses pesanan ini di:\n";
$admin_message .= "https://geraitanobatak.com/admin/orders.php";

echo "<pre>$admin_message</pre>";
echo "<p>Panjang pesan: " . strlen($admin_message) . " karakter</p>";

// Delay sebentar
sleep(3);

$result2 = sendKirimiMessage($admin_phone, $admin_message);

echo "<strong>Hasil:</strong><br>";
echo "Status: " . $result2['status'] . "<br>";
echo "Error: " . ($result2['error'] ?: 'Tidak ada') . "<br>";
echo "Response: <pre>" . print_r($result2['data'], true) . "</pre>";

if ($result2['status'] == 200) {
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ PESAN BERHASIL DIKIRIM KE ADMIN!</p>";
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ PESAN GAGAL DIKIRIM!</p>";
    
    echo "<h3>Kemungkinan Masalah:</h3>";
    echo "<ul>";
    echo "<li>Device WhatsApp tidak aktif di dashboard Kirimi</li>";
    echo "<li>Nomor admin belum disimpan di kontak WhatsApp device</li>";
    echo "<li>Rate limiting - terlalu banyak pesan dalam waktu singkat</li>";
    echo "<li>Pesan terlalu panjang (max biasanya 4096 karakter)</li>";
    echo "<li>Secret Key atau User Code salah</li>";
    echo "</ul>";
}
?>
