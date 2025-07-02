<?php
define('DB_HOST', '103.200.23.120');
define('DB_USER', 'nrojunec_store');
define('DB_PASS', 'Quocdu@123');
define('DB_NAME', 'nrojunec_store');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); 