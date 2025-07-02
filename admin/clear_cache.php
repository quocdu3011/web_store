<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Clear any temporary files or cache folders
$message = '';
$error = '';

try {
    // Example: Clear uploads/temp directory
    $temp_dir = '../uploads/temp';
    if (is_dir($temp_dir)) {
        $files = glob($temp_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Example: Clear any session data except admin sessions
    $stmt = $conn->prepare("DELETE FROM sessions WHERE user_id NOT IN (SELECT id FROM users WHERE is_admin = 1)");
    if ($stmt) {
        $stmt->execute();
    }

    // Example: Clear any database cache
    $stmt = $conn->prepare("DELETE FROM cache WHERE expiry < NOW()");
    if ($stmt) {
        $stmt->execute();
    }

    $message = 'Xóa cache thành công!';
} catch (Exception $e) {
    $error = 'Lỗi khi xóa cache: ' . $e->getMessage();
}

// Redirect back to settings page with message
if ($error) {
    header('Location: settings.php?error=' . urlencode($error));
} else {
    header('Location: settings.php?message=' . urlencode($message));
}
exit; 