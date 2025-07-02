<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_role') {
        $user_id = $_POST['user_id'] ?? null;
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Prevent locking yourself out
        if ($user_id == $_SESSION['user_id'] && $is_admin == 0) {
            $error = 'Không thể gỡ quyền quản trị của chính mình';
        } else {
            $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
            $stmt->bind_param("ii", $is_admin, $user_id);
            
            if ($stmt->execute()) {
                $message = 'Cập nhật quyền người dùng thành công';
            } else {
                $error = 'Lỗi: ' . $stmt->error;
            }
        }
    } elseif ($action == 'delete') {
        $user_id = $_POST['user_id'] ?? null;
        
        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            $error = 'Không thể xóa tài khoản của chính mình';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $message = 'Xóa người dùng thành công';
            } else {
                $error = 'Lỗi: ' . $stmt->error;
            }
        }
    }
}

// Get users with filter
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

$sql = "SELECT * FROM users";
$params = [];
$param_types = "";

// Add filters
$where_clauses = [];

if (!empty($role_filter)) {
    if ($role_filter == 'admin') {
        $where_clauses[] = "is_admin = 1";
    } else {
        $where_clauses[] = "is_admin = 0";
    }
}

if (!empty($search_query)) {
    $search_query = "%$search_query%";
    $where_clauses[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $params[] = $search_query;
    $params[] = $search_query;
    $params[] = $search_query;
    $param_types .= "sss";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng - Quản Trị</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1>Quản Lý Người Dùng</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                <?php endif; ?>

                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="role" class="form-label">Vai Trò</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tất cả</option>
                                    <option value="admin" <?php if ($role_filter == 'admin') echo 'selected'; ?>>Quản trị viên</option>
                                    <option value="user" <?php if ($role_filter == 'user') echo 'selected'; ?>>Người dùng</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Tìm Kiếm</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo sanitize_output($search_query); ?>" placeholder="Tên người dùng, email...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Lọc</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="alert alert-info">Không có người dùng nào khớp với điều kiện tìm kiếm.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên Người Dùng</th>
                                            <th>Email</th>
                                            <th>Họ Tên</th>
                                            <th>Ngày Đăng Ký</th>
                                            <th>Vai Trò</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo sanitize_output($user['username']); ?></td>
                                                <td><?php echo sanitize_output($user['email']); ?></td>
                                                <td><?php echo sanitize_output($user['full_name'] ?? ''); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['is_admin'] ? 'primary' : 'secondary'; ?>">
                                                        <?php echo $user['is_admin'] ? 'Quản trị viên' : 'Người dùng'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-user" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editUserModal"
                                                            data-user-id="<?php echo $user['id']; ?>"
                                                            data-username="<?php echo sanitize_output($user['username']); ?>"
                                                            data-is-admin="<?php echo $user['is_admin']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <button class="btn btn-sm btn-danger delete-user"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteUserModal"
                                                                data-user-id="<?php echo $user['id']; ?>"
                                                                data-username="<?php echo sanitize_output($user['username']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cập Nhật Người Dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" id="edit-user-id">

                        <div class="mb-3">
                            <label class="form-label">Tên Người Dùng</label>
                            <input type="text" class="form-control" id="edit-username" disabled>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is-admin" name="is_admin">
                            <label class="form-check-label" for="is-admin">Quản trị viên</label>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Cập Nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác Nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa người dùng này?</p>
                    <p class="fw-bold" id="delete-username"></p>
                    <p class="text-danger">Thao tác này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="delete-user-id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit user role
            const editButtons = document.querySelectorAll('.edit-user');
            const editUserId = document.getElementById('edit-user-id');
            const editUsername = document.getElementById('edit-username');
            const isAdmin = document.getElementById('is-admin');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    editUserId.value = this.dataset.userId;
                    editUsername.value = this.dataset.username;
                    isAdmin.checked = this.dataset.isAdmin === '1';
                });
            });
            
            // Delete user
            const deleteButtons = document.querySelectorAll('.delete-user');
            const deleteUserId = document.getElementById('delete-user-id');
            const deleteUsername = document.getElementById('delete-username');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteUserId.value = this.dataset.userId;
                    deleteUsername.textContent = this.dataset.username;
                });
            });
        });
    </script>
</body>
</html> 