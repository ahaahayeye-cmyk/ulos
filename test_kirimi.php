<?php
require_once 'includes/kirimi_config.php';

echo "<h2>Test Kirimi API</h2>";

// Test kirim pesan ke admin
$admin_phone = '6281317975623';
$test_message = "🧪 *Test Pesan dari Sistem*\n\n";
$test_message .= "Ini adalah pesan test untuk memastikan notifikasi admin berfungsi.\n\n";
$test_message .= "Waktu: " . date('d/m/Y H:i:s');

echo "<p>Mengirim pesan test ke admin: $admin_phone</p>";
echo "<p>Device ID: " . KIRIMI_DEVICE_ID . "</p>";
echo "<p>User Code: " . KIRIMI_USER_CODE . "</p>";

$result = sendKirimiMessage($admin_phone, $test_message);

echo "<h3>Hasil:</h3>";
echo "<pre>";
echo "Status Code: " . $result['status'] . "\n";
echo "Error: " . ($result['error'] ?: 'Tidak ada error') . "\n";
echo "Response Data: " . print_r($result['data'], true) . "\n";
echo "</pre>";

if ($result['status'] == 200) {
    echo "<p style='color: green; font-weight: bold;'>✅ Pesan berhasil dikirim!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Pesan gagal dikirim!</p>";
    echo "<p>Kemungkinan masalah:</p>";
    echo "<ul>";
    echo "<li>Device ID salah atau tidak aktif</li>";
    echo "<li>WhatsApp device belum terhubung di dashboard Kirimi</li>";
    echo "<li>Secret Key atau User Code salah</li>";
    echo "<li>Nomor admin tidak valid</li>";
    echo "</ul>";
}
?>
