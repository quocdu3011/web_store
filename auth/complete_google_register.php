<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['google_register'])) {
    header('Location: ../login.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $birth_date = $_POST['birth_date'];
    $phone = trim($_POST['phone']);
    $email = $_SESSION['google_register']['email'] ?? '';
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['username'] ?? ''));
    $avatar = $_SESSION['google_register']['avatar'] ?? null;
    $social_id = $_SESSION['google_register']['social_id'];
    $social_type = $_SESSION['google_register']['social_type'];
    $role = 'customer';
    $password = '';
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender'] ?? null;

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $err = "Tên đăng nhập đã tồn tại, vui lòng chọn tên khác.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users 
        (username, email, password, full_name, birth_date, role, social_id, social_type, avatar, phone, address, gender) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", 
            $username, $email, $password, $full_name, $birth_date, $role,
            $social_id, $social_type, $avatar, $phone, $address, $gender
        );

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['success_message'] = "Đăng ký thành công! Chào mừng bạn, $full_name";
            unset($_SESSION['google_register']);
            header('Location: ../index.php');
            exit;
        } else {
            $err = "Đã xảy ra lỗi khi tạo tài khoản.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Hoàn tất đăng ký</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="assets/js/main.js"></script>
  <style>
    body {
      background-color: #f7f9fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .form-container {
      max-width: 720px;
      margin: auto;
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
      box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.2);
    }
    .form-check-input:checked {
      background-color: #dc3545;
      border-color: #dc3545;
    }
    .btn-red {
      background-color: #dc3545;
      border: none;
    }
    .btn-red:hover {
      background-color: #b02a37;
    }
  </style>
</head>
<body>
  <div class="container my-5">
    <div class="form-container">
      <h2 class="text-center text-danger fw-bold mb-4">Cập nhật thông tin</h2>

      <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?php echo $err; ?></div>
      <?php endif; ?>

      <form method="post" id="registerForm">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Họ và tên</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Ngày sinh</label>
            <input type="date" name="birth_date" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Số điện thoại</label>
            <input type="tel" name="phone" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control bg-light text-muted" value="<?php echo htmlspecialchars($_SESSION['google_register']['email'] ?? '') ?>" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" placeholder="Tên không dấu, không cách" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Giới tính</label>
            <select name="gender" class="form-select">
              <option value="">Chọn</option>
              <option value="Nam">Nam</option>
              <option value="Nữ">Nữ</option>
              <option value="Khác">Khác</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Địa chỉ</label>
            <input type="text" name="address" class="form-control" placeholder="Số nhà, đường, tỉnh/thành...">
          </div>
        </div>

        <p class="mt-4 small text-muted">
          Bằng việc Đăng ký, bạn đồng ý với
          <a href="#" class="text-primary">Điều khoản sử dụng</a> và
          <a href="#" class="text-primary">Chính sách bảo mật</a>.
        </p>

        <div class="row mt-4">
          <div class="col-md-6">
            <a href="../login.php" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-left"></i> Quay lại đăng nhập
            </a>
          </div>
          <div class="col-md-6">
            <button type="submit" class="btn btn-red w-100 fw-semibold">Hoàn tất đăng ký</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById("registerForm").addEventListener("submit", function(e) {
      const username = document.querySelector('[name="username"]');
      if (username.value.trim().length < 4) {
        e.preventDefault();
        showToast("Tên đăng nhập phải có ít nhất 4 ký tự.","error");
      }
    });
  </script>
</body>
</html>
