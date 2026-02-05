<?php
// Script untuk memperbaiki permission direktori upload

echo "<h2>Memperbaiki Permission Direktori Upload</h2>";

$directories = [
    'uploads/',
    'uploads/products/'
];

foreach ($directories as $dir) {
    echo "<h3>Memeriksa direktori: $dir</h3>";
    
    // Buat direktori jika belum ada
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Direktori $dir berhasil dibuat<br>";
        } else {
            echo "❌ Gagal membuat direktori $dir<br>";
            continue;
        }
    } else {
        echo "✅ Direktori $dir sudah ada<br>";
    }
    
    // Set permission
    if (chmod($dir, 0755)) {
        echo "✅ Permission direktori $dir berhasil diset ke 0755<br>";
    } else {
        echo "❌ Gagal mengubah permission direktori $dir<br>";
    }
    
    // Test write permission
    $test_file = $dir . 'test_write_' . time() . '.txt';
    if (file_put_contents($test_file, 'test')) {
        echo "✅ Test write ke $dir berhasil<br>";
        unlink($test_file); // hapus file test
    } else {
        echo "❌ Test write ke $dir gagal<br>";
    }
    
    echo "<br>";
}

// Periksa .htaccess di uploads
$htaccess_file = 'uploads/.htaccess';
if (!file_exists($htaccess_file)) {
    $htaccess_content = '# Izinkan akses ke gambar
<Files ~ "\.(jpg|jpeg|png|gif|svg|webp)$">
    Order allow,deny
    Allow from all
</Files>

# Blokir akses ke file PHP
<Files ~ "\.php$">
    Order deny,allow
    Deny from all
</Files>';
    
    if (file_put_contents($htaccess_file, $htaccess_content)) {
        echo "✅ File .htaccess berhasil dibuat di uploads/<br>";
    } else {
        echo "❌ Gagal membuat file .htaccess di uploads/<br>";
    }
} else {
    echo "✅ File .htaccess sudah ada di uploads/<br>";
}

echo "<h3>Informasi Server</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "File Uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";
echo "Upload Tmp Dir: " . (ini_get('upload_tmp_dir') ?: 'Default') . "<br>";

echo "<h3>Test Upload Form</h3>";
?>

<form method="POST" enctype="multipart/form-data" style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
    <label>Test Upload File:</label><br>
    <input type="file" name="test_upload" accept="image/*"><br><br>
    <button type="submit" name="test_submit">Test Upload</button>
</form>

<?php
if (isset($_POST['test_submit']) && isset($_FILES['test_upload'])) {
    echo "<h4>Hasil Test Upload:</h4>";
    
    $file = $_FILES['test_upload'];
    echo "Nama file: " . $file['name'] . "<br>";
    echo "Ukuran: " . $file['size'] . " bytes<br>";
    echo "Type: " . $file['type'] . "<br>";
    echo "Error code: " . $file['error'] . "<br>";
    echo "Tmp name: " . $file['tmp_name'] . "<br>";
    
    if ($file['error'] == UPLOAD_ERR_OK) {
        $target = 'uploads/test_' . time() . '_' . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $target)) {
            echo "✅ Upload berhasil ke: $target<br>";
            // Hapus file test
            unlink($target);
            echo "✅ File test berhasil dihapus<br>";
        } else {
            echo "❌ Gagal memindahkan file<br>";
        }
    } else {
        echo "❌ Error upload: ";
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo "File terlalu besar (upload_max_filesize)";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "File terlalu besar (MAX_FILE_SIZE)";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "File hanya terupload sebagian";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "Tidak ada file yang diupload";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Direktori temporary tidak ada";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Gagal menulis file ke disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "Upload dihentikan oleh ekstensi PHP";
                break;
            default:
                echo "Error tidak dikenal: " . $file['error'];
        }
        echo "<br>";
    }
}
?>