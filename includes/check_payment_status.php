<?php
// Ngăn hiển thị lỗi PHP
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/database.php';

// Đảm bảo id là số
$order_id = (int) ($_GET['id'] ?? 0);

header('Content-Type: application/json');

if ($order_id <= 0) {
    echo json_encode(['paid' => false]);
    exit;
}

$stmt = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'paid' => $result && $result['payment_status'] === 'paid'
]);
exit;