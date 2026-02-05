<?php
echo "<h2>Fix Permissions untuk XAMPP Windows</h2>";

$directories = [
    'uploads',
    'uploads/products'
];

echo "<h3>Memperbaiki Direktori Upload:</h3>";

foreach ($directories as $dir) {
    echo "<h4>Processing: $dir</h4>";
    
    // Buat direktori jika belum ada
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "✅ Direktori $dir berhasil dibuat<br>";
        } else {
            echo "❌ Gagal membuat direktori $dir<br>";
            continue;
        }
    } else {
        echo "✅ Direktori $dir sudah ada<br>";
    }
    
    // Set permission untuk Windows
    if (chmod($dir, 0777)) {
        echo "✅ Permission $dir berhasil diset ke 0777<br>";
    } else {
        echo "⚠️ Gagal mengubah permission $dir (mungkin tidak diperlukan di Windows)<br>";
    }
    
    // Test write
    $testFile = $dir . '/test_' . time() . '.txt';
    if (file_put_contents($testFile, 'test content')) {
        echo "✅ Test write berhasil di $dir<br>";
        unlink($testFile);
    } else {
        echo "❌ Test write gagal di $dir<br>";
    }
    
    echo "<br>";
}

// Buat .htaccess yang benar
echo "<h3>Membuat .htaccess:</h3>";
$htaccessContent = '# Allow access to images
<Files ~ "\.(jpg|jpeg|png|gif|svg|webp|bmp|tiff)$">
    Order allow,deny
    Allow from all
</Files>

# Block access to PHP files for security
<Files ~ "\.php$">
    Order deny,allow
    Deny from all
</Files>

# Block access to sensitive files
<Files ~ "\.(txt|log|ini|conf)$">
    Order deny,allow
    Deny from all
</Files>';

$htaccessFile = 'uploads/.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "✅ File .htaccess berhasil dibuat/diupdate<br>";
} else {
    echo "❌ Gagal membuat file .htaccess<br>";
}

// Test upload sederhana
echo "<h3>Test Upload Sederhana:</h3>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fix_test_file'])) {
    $file = $_FILES['fix_test_file'];
    
    echo "File info:<br>";
    echo "- Name: " . $file['name'] . "<br>";
    echo "- Size: " . $file['size'] . " bytes<br>";
    echo "- Error: " . $file['error'] . "<br>";
    echo "- Tmp: " . $file['tmp_name'] . "<br>";
    
    if ($file['error'] == UPLOAD_ERR_OK) {
        $targetFile = 'uploads/fix_test_' . time() . '_' . basename($file['name']);
        
        echo "Target: $targetFile<br>";
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            echo "<div style='color: green; font-weight: bold;'>✅ UPLOAD BERHASIL!</div>";
            echo "File berhasil disimpan ke: $targetFile<br>";
            
            // Test akses file
            if (file_exists($targetFile)) {
                echo "✅ File dapat diakses<br>";
                echo "Size: " . filesize($targetFile) . " bytes<br>";
                
                // Tampilkan jika gambar
                $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                if (in_array($ext, $imageTypes)) {
                    echo "<br><img src='$targetFile' style='max-width: 200px; border: 1px solid #ccc;'><br>";
                }
            }
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ UPLOAD GAGAL!</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ ERROR: " . $file['error'] . "</div>";
    }
}
?>

<form method="POST" enctype="multipart/form-data" style="border: 1px solid #ccc; padding: 20px; background: #f0f8ff;">
    <h4>Test Upload File:</h4>
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
    <input type="file" name="fix_test_file" accept="image/*" required><br><br>
    <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none;">Test Upload</button>
</form>

<h3>Langkah Manual untuk Windows:</h3>
<ol>
    <li>Klik kanan pada folder <code>uploads</code> → Properties</li>
    <li>Uncheck "Read-only" jika dicentang</li>
    <li>Pada tab Security, pastikan "Users" memiliki "Full control"</li>
    <li>Restart Apache di XAMPP Control Panel</li>
    <li>Jalankan XAMPP sebagai Administrator jika perlu</li>
</ol>

<h3>Files di direktori uploads:</h3>
<?php
if (is_dir('uploads')) {
    $files = scandir('uploads');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = 'uploads/' . $file;
            $size = is_file($filePath) ? filesize($filePath) : 'DIR';
            echo "<li>$file ($size bytes)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "Direktori uploads tidak ada!";
}
?>