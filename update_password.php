<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Lấy mật khẩu hiện tại từ DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = 'Mật khẩu hiện tại không đúng.';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Mật khẩu mới và xác nhận không trùng khớp.';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        // Hash mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Đổi mật khẩu thành công.';
        } else {
            $_SESSION['error'] = 'Có lỗi khi đổi mật khẩu.';
        }
    }
}

// Quay lại account.php
header('Location: account.php');
exit;
?>
