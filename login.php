<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        if (login_user($email, $password)) {
            $_SESSION['success_message'] = 'Đăng nhập thành công!';
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập - Tech Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7f9fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-container {
      max-width: 450px;
      margin: 6% auto;
      background: #fff;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .form-control {
      border-radius: 12px;
      height: 48px;
    }
    .form-control:focus {
      border-color: #dc3545;
      box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.25);
    }
    .btn-red {
      background-color: #dc3545;
      color: #fff;
      border: none;
    }
    .btn-red:hover {
      background-color: #b02a37;
    }
    .social-login-btn {
      border-radius: 8px;
      border: 1px solid #ccc;
      padding: 8px 16px;
      font-weight: 500;
      width: 48%;
    }
    .social-login-btn i {
      margin-right: 8px;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h3 class="text-center text-danger fw-bold mb-4">Đăng nhập tài khoản</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập email">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Mật khẩu</label>
      <input type="password" class="form-control" id="password" name="password" required placeholder="Nhập mật khẩu">
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="remember">
      <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
    </div>
    <button type="submit" class="btn btn-red w-100 fw-semibold">Đăng nhập</button>
  </form>

  <div class="text-center my-4">
    <p class="text-muted">Hoặc đăng nhập bằng</p>
    <div class="d-flex justify-content-center gap-3">
      <a href="auth/google_login.php" class="btn social-login-btn"><i class="bi bi-google"></i> Google</a>
      <a href="auth/facebook_login.php" class="btn social-login-btn"><i class="bi bi-facebook"></i> Facebook</a>
    </div>
  </div>

  <div class="text-center mt-3">
    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    <p><a href="forgot-password.php">Quên mật khẩu?</a></p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
