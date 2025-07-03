<?php
session_start();
require_once '../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'login' => true,
        'message' => 'Vui lòng đăng nhập để tiếp tục.'
    ]);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu hoặc sai mã sản phẩm.']);
    exit;
}

// Remove from cart
remove_from_cart($product_id);

// Return updated info
echo json_encode([
    'success' => true,
    'cartCount' => get_cart_count(),
    'cartTotal' => format_price(get_cart_total())
]);
