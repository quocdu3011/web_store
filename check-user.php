<?php
require_once 'config/database.php';

$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');

if ($field === 'username') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
} elseif ($field === 'email') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
} else {
    http_response_code(400);
    exit('Invalid field');
}

$stmt->bind_param("s", $value);
$stmt->execute();
$stmt->store_result();

echo $stmt->num_rows > 0 ? 'taken' : 'available';
exit;
