<?php
// Test konfigurasi upload file
echo "<h2>Konfigurasi Upload File PHP</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

$upload_settings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

foreach ($upload_settings as $setting => $value) {
    echo "<tr><td>$setting</td><td>" . ($value ? $value : 'Not set') . "</td></tr>";
}

echo "</table>";

// Test permission direktori uploads
echo "<h3>Test Permission Direktori</h3>";
$upload_dir = 'uploads/';

if (is_dir($upload_dir)) {
    echo "✅ Direktori uploads ada<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Direktori uploads dapat ditulis<br>";
    } else {
        echo "❌ Direktori uploads tidak dapat ditulis<br>";
    }
} else {
    echo "❌ Direktori uploads tidak ada<br>";
}

// Test permission direktori uploads/products
$products_dir = 'uploads/products/';
if (is_dir($products_dir)) {
    echo "✅ Direktori uploads/products ada<br>";
    if (is_writable($products_dir)) {
        echo "✅ Direktori uploads/products dapat ditulis<br>";
    } else {
        echo "❌ Direktori uploads/products tidak dapat ditulis<br>";
    }
} else {
    echo "❌ Direktori uploads/products tidak ada<br>";
}

// Test membuat file test
$test_file = $upload_dir . 'test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ Berhasil membuat file test<br>";
    unlink($test_file); // hapus file test
} else {
    echo "❌ Gagal membuat file test<br>";
}

echo "<h3>Error Codes Upload</h3>";
echo "<ul>";
echo "<li>UPLOAD_ERR_OK (0): Upload berhasil</li>";
echo "<li>UPLOAD_ERR_INI_SIZE (1): File terlalu besar (upload_max_filesize)</li>";
echo "<li>UPLOAD_ERR_FORM_SIZE (2): File terlalu besar (MAX_FILE_SIZE)</li>";
echo "<li>UPLOAD_ERR_PARTIAL (3): File hanya terupload sebagian</li>";
echo "<li>UPLOAD_ERR_NO_FILE (4): Tidak ada file yang diupload</li>";
echo "<li>UPLOAD_ERR_NO_TMP_DIR (6): Tidak ada direktori temporary</li>";
echo "<li>UPLOAD_ERR_CANT_WRITE (7): Gagal menulis file ke disk</li>";
echo "<li>UPLOAD_ERR_EXTENSION (8): Upload dihentikan oleh ekstensi PHP</li>";
echo "</ul>";
?>