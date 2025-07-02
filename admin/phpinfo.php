<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Display PHP information
phpinfo(); 