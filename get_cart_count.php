<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $count = $result['total'] ? (int)$result['total'] : 0;
    } catch (Exception $e) {
        $count = 0;
    }
}

echo json_encode(['count' => $count]);
?>