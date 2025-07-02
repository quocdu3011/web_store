<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';


    // Sanitize input
    $full_name = trim(htmlspecialchars($full_name));
    $phone = trim(htmlspecialchars($phone));
    $address = trim(htmlspecialchars($address));
    $gender = trim(htmlspecialchars($gender));

    $allowed_genders = ['Nam', 'Nữ', 'Khác', ''];
    if (!in_array($gender, $allowed_genders)) {
        $_SESSION['error'] = 'Giá trị giới tính không hợp lệ';
        header('Location: account.php');
        exit;
    }


    // Update user information
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, gender = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $phone, $address, $gender, $_SESSION['user_id']);


    if ($stmt->execute()) {
        $_SESSION['message'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update profile';
    }
}

// Redirect back to account page
header('Location: account.php');
exit;