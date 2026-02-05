<?php
echo "<h2>Check Permissions untuk XAMPP Windows</h2>";

// Informasi sistem
echo "<h3>Informasi Sistem:</h3>";
echo "OS: " . PHP_OS . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current User: " . get_current_user() . "<br>";

// Fungsi untuk check permission detail
function checkPermissions($path) {
    echo "<h4>Checking: $path</h4>";
    
    if (!file_exists($path)) {
        echo "❌ Path tidak ada<br>";
        return false;
    }
    
    $realPath = realpath($path);
    echo "Real Path: $realPath<br>";
    
    // Basic checks
    echo "Exists: " . (file_exists($path) ? '✅' : '❌') . "<br>";
    echo "Is Directory: " . (is_dir($path) ? '✅' : '❌') . "<br>";
    echo "Is Readable: " . (is_readable($path) ? '✅' : '❌') . "<br>";
    echo "Is Writable: " . (is_writable($path) ? '✅' : '❌') . "<br>";
    
    // File permissions (octal)
    $perms = fileperms($path);
    echo "Permissions (octal): " . sprintf('%o', $perms) . "<br>";
    
    // Owner info
    $owner = fileowner($path);
    $group = filegroup($path);
    echo "Owner UID: $owner<br>";
    echo "Group GID: $group<br>";
    
    // Test write dengan file
    if (is_dir($path)) {
        $testFile = $path . DIRECTORY_SEPARATOR . 'test_write_' . time() . '.txt';
        if (file_put_contents($testFile, 'test')) {
            echo "Write Test: ✅ SUCCESS<br>";
            unlink($testFile);
        } else {
            echo "Write Test: ❌ FAILED<br>";
        }
    }
    
    echo "<br>";
    return true;
}

// Check berbagai path
$pathsToCheck = [
    __DIR__,
    __DIR__ . DIRECTORY_SEPARATOR . 'uploads',
    __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products',
    __DIR__ . DIRECTORY_SEPARATOR . 'admin',
];

foreach ($pathsToCheck as $path) {
    checkPermissions($path);
}

// Test create directory
echo "<h3>Test Create Directory:</h3>";
$testDir = __DIR__ . DIRECTORY_SEPARATOR . 'test_dir_' . time();
if (mkdir($testDir, 0755)) {
    echo "✅ Berhasil membuat direktori: $testDir<br>";
    
    // Test write file di direktori baru
    $testFile = $testDir . DIRECTORY_SEPARATOR . 'test.txt';
    if (file_put_contents($testFile, 'test content')) {
        echo "✅ Berhasil menulis file di direktori baru<br>";
        unlink($testFile);
    } else {
        echo "❌ Gagal menulis file di direktori baru<br>";
    }
    
    rmdir($testDir);
    echo "✅ Direktori test berhasil dihapus<br>";
} else {
    echo "❌ Gagal membuat direktori test<br>";
}

// Check PHP upload settings
echo "<h3>PHP Upload Settings:</h3>";
$uploadSettings = [
    'file_uploads',
    'upload_max_filesize', 
    'post_max_size',
    'max_file_uploads',
    'upload_tmp_dir',
    'memory_limit',
    'max_execution_time',
    'max_input_time'
];

foreach ($uploadSettings as $setting) {
    $value = ini_get($setting);
    echo "$setting: " . ($value ?: 'Not set') . "<br>";
}

// Check temp directory
echo "<h3>Temp Directory:</h3>";
$tempDir = sys_get_temp_dir();
echo "System Temp Dir: $tempDir<br>";
checkPermissions($tempDir);

// Check upload temp dir
$uploadTempDir = ini_get('upload_tmp_dir');
if ($uploadTempDir) {
    echo "<h3>Upload Temp Directory:</h3>";
    checkPermissions($uploadTempDir);
}

echo "<h3>Rekomendasi untuk XAMPP Windows:</h3>";
echo "<ul>";
echo "<li>Pastikan XAMPP dijalankan sebagai Administrator</li>";
echo "<li>Periksa Windows Defender atau antivirus yang mungkin memblokir</li>";
echo "<li>Pastikan direktori uploads tidak read-only</li>";
echo "<li>Coba restart Apache setelah mengubah permission</li>";
echo "</ul>";
?>