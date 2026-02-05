<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;

if (!$image_id) {
    echo json_encode(['success' => false, 'message' => 'Image ID required']);
    exit();
}

try {
    // Get image info before deleting
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if (!$image) {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        exit();
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $result = $stmt->execute([$image_id]);
    
    if ($result) {
        // Delete physical file
        $file_path = '../uploads/' . $image['image_path'];
        if (file_exists($file_path)) {
            // Don't delete default images
            if (!in_array($image['image_path'], ['ulos1.svg', 'ulos2.svg', 'ulos3.svg', 'ulos4.svg', 'ulos5.svg'])) {
                unlink($file_path);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>