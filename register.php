<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ các trường bắt buộc.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu không khớp.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        if (register_user($username, $email, $password, $full_name)) {
            $success = 'Đăng ký thành công! Bạn có thể đăng nhập.';
        } else {
            $error = 'Đăng ký thất bại. Email hoặc tên đăng nhập đã tồn tại.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Tech Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', sans-serif;
        }

        .register-container {
            max-width: 540px;
            margin: 5% auto;
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
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
<script>
    function checkAvailability(field, value, feedbackId) {
        if (!value) return;

        fetch(`check-user.php?field=${field}&value=${encodeURIComponent(value)}`)
            .then(response => response.text())
            .then(result => {
                const feedback = document.getElementById(feedbackId);
                const input = document.getElementById(field);

                if (result === 'taken') {
                    feedback.textContent = field === 'email' ? 'Email đã được sử dụng.' : 'Tên đăng nhập đã tồn tại.';
                    feedback.style.color = 'red';
                    input.classList.add('is-invalid');
                } else {
                    feedback.textContent = '✓ Hợp lệ';
                    feedback.style.color = 'green';
                    input.classList.remove('is-invalid');
                }
            });
    }

    document.getElementById('username').addEventListener('blur', function () {
        checkAvailability('username', this.value, 'usernameFeedback');
    });

    document.getElementById('email').addEventListener('blur', function () {
        checkAvailability('email', this.value, 'emailFeedback');
    });
</script>

<body>

    <div class="register-container">
        <h3 class="text-center text-danger fw-bold mb-4">Tạo tài khoản mới</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập *</label>
                <input type="text" class="form-control" id="username" name="username" required>
                <div id="usernameFeedback" class="form-text"></div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div id="emailFeedback" class="form-text"></div>
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">Họ và tên</label>
                <input type="text" class="form-control" id="full_name" name="full_name">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu *</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="text-muted">Ít nhất 6 ký tự</small>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu *</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">Tôi đồng ý với <a href="terms.php">Điều khoản sử
                        dụng</a></label>
            </div>
            <button type="submit" class="btn btn-red w-100 fw-semibold">Đăng ký</button>
        </form>

        <div class="text-center my-4">
            <p class="text-muted">Hoặc đăng ký bằng</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="auth/google_login.php" class="btn social-login-btn"><i class="bi bi-google"></i> Google</a>
                <a href="auth/facebook_login.php" class="btn social-login-btn"><i class="bi bi-facebook"></i>
                    Facebook</a>
            </div>
        </div>

        <div class="text-center mt-3">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>