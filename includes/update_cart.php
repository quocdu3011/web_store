<?php
session_start();
require_once '../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;

if (!$product_id || !$quantity) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required']);
    exit;
}

// Check if product exists and has a price
$product = get_product($product_id);
if (!$product || $product['price'] == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Update cart quantity
update_cart_quantity($product_id, $quantity);

// Return updated cart info
echo json_encode([
    'success' => true,
    'message' => 'Cart updated successfully',
    'cartCount' => get_cart_count(),
    'total' => format_price(get_cart_total())
]); 