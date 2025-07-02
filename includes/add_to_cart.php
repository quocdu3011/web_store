<?php
session_start();
require_once '../config/database.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'login' => true,
        'message' => 'User not logged in'
    ]);
    exit;
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

// Check if product exists and has a price
$product = get_product($product_id);
if (!$product || $product['price'] == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Add to cart
add_to_cart($product_id, $quantity);

// Return updated cart info
echo json_encode([
    'success' => true,
    'message' => 'Product added to cart successfully',
    'cartCount' => get_cart_count(),
    'cartTotal' => format_price(get_cart_total())
]); 