<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT spec_name, spec_value FROM product_specs WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $specs = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($specs);
} else {
    echo json_encode([]);
}
