<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle updates and deletions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? null;

    if ($action === 'update_role') {
        $role = $_POST['role'] ?? 'customer';

        if ($user_id == $_SESSION['user_id'] && $role !== 'admin') {
            $error = 'Bạn không thể gỡ quyền quản trị của chính mình.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $user_id);
            $stmt->execute() ? $message = 'Cập nhật vai trò thành công.' : $error = $stmt->error;
        }
    } elseif ($action === 'delete') {
        if ($user_id == $_SESSION['user_id']) {
            $error = 'Bạn không thể tự xóa tài khoản của mình.';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute() ? $message = 'Xóa người dùng thành công.' : $error = $stmt->error;
        }
    }
}

$role_filter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT id, username, full_name, email, role, created_at FROM users WHERE 1";
$params = [];
$types = '';

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/admin_header.php'; ?>
<div class="container-fluid">
  <div class="row">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <h2 class="mb-4">Danh sách người dùng</h2>

      <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
          <select class="form-select" name="role">
            <option value="">Tất cả vai trò</option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="staff" <?= $role_filter === 'staff' ? 'selected' : '' ?>>Nhân viên</option>
            <option value="customer" <?= $role_filter === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
          </select>
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tên, email...">
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100">Lọc</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Tên đăng nhập</th>
              <th>Họ tên</th>
              <th>Email</th>
              <th>Vai trò</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?= $user['id'] ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['full_name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td>
                <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'staff' ? 'warning' : 'secondary') ?>">
                  <?= ucfirst($user['role']) ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="update_role">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <select name="role" class="form-select form-select-sm d-inline w-auto">
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Nhân viên</option>
                    <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Khách</option>
                  </select>
                  <button class="btn btn-sm btn-success">Lưu</button>
                </form>
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Xác nhận xóa người dùng này?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button class="btn btn-sm btn-danger">Xóa</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
