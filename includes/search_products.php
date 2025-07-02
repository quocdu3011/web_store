<?php
session_start();
require_once '../config/database.php';
require_once 'functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Get search query from request
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['products' => []]);
    exit;
}

// Search for products matching the query
$search = '%' . $conn->real_escape_string($query) . '%';
$sql = "SELECT p.id, p.name, p.price, p.image, p.slug, c.name as child_name, c.slug as child_slug, pcat.name as parent_name, pcat.slug as parent_slug
        FROM products p
        LEFT JOIN child_categories c ON p.category_id = c.id
        LEFT JOIN parent_categories pcat ON c.parent_id = pcat.id
        WHERE p.name LIKE ? OR p.description LIKE ? OR p.manufacturer LIKE ? 
        ORDER BY p.name ASC LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => number_format($row['price'], 0, ',', '.') . '₫',
        'image' => $row['image'],
        'url' => 'product.php?slug=' . $row['slug'],
        'category' => [
            'child_name' => $row['child_name'],
            'child_slug' => $row['child_slug'],
            'parent_name' => $row['parent_name'],
            'parent_slug' => $row['parent_slug'],
        ]
    ];
}

echo json_encode(['products' => $products]); 