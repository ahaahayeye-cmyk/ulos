<?php
// Debug script untuk upload
echo "<h2>Debug Upload Path</h2>";

// Current working directory
echo "Current working directory: " . getcwd() . "<br>";

// Check upload directory paths
$paths_to_check = [
    'uploads/',
    '../uploads/',
    './uploads/',
    __DIR__ . '/uploads/',
    __DIR__ . '/../uploads/'
];

foreach ($paths_to_check as $path) {
    echo "<h3>Path: $path</h3>";
    echo "Absolute path: " . realpath($path) . "<br>";
    echo "Exists: " . (is_dir($path) ? 'Yes' : 'No') . "<br>";
    echo "Writable: " . (is_writable($path) ? 'Yes' : 'No') . "<br>";
    echo "Readable: " . (is_readable($path) ? 'Yes' : 'No') . "<br>";
    echo "<br>";
}

// Check from admin directory perspective
echo "<h3>From admin/ directory perspective:</h3>";
$admin_upload_path = '../uploads/';
echo "Path: $admin_upload_path<br>";
echo "Absolute path: " . realpath($admin_upload_path) . "<br>";
echo "Exists: " . (is_dir($admin_upload_path) ? 'Yes' : 'No') . "<br>";
echo "Writable: " . (is_writable($admin_upload_path) ? 'Yes' : 'No') . "<br>";

// Test file creation
$test_file = $admin_upload_path . 'test_debug.txt';
if (file_put_contents($test_file, 'debug test')) {
    echo "✅ Can create file in $admin_upload_path<br>";
    unlink($test_file);
} else {
    echo "❌ Cannot create file in $admin_upload_path<br>";
}

// Check PHP upload settings
echo "<h3>PHP Upload Settings:</h3>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'System default') . "<br>";

// Check if tmp dir is writable
$tmp_dir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
echo "Temp directory: $tmp_dir<br>";
echo "Temp dir writable: " . (is_writable($tmp_dir) ? 'Yes' : 'No') . "<br>";
?>