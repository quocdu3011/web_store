<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_SESSION['user_id'])) {
    $order_id = (int) $_POST['order_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    $_SESSION['success_message'] = 'Đơn hàng đã được hủy.';
}

header("Location: my_orders.php");
exit;
