<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's orders (if you have an orders table)
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .account-sidebar .card {
            border-radius: 0.5rem;
            border: 2px solid #198754;
            /* Bootstrap success */
        }

        .account-sidebar .card-header {
            background-color: #198754;
            color: #fff;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .account-sidebar .list-group-item.active {
            background-color: #198754;
            border: none;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); endif; ?>


    <div class="container my-4">
        <div class="row">
            <!-- Account Sidebar -->
            <!-- Account Sidebar -->
            <div class="col-md-3">
                <div class="card shadow-sm rounded border border-success">
                    <div class="card-header bg-success text-white rounded-top">
                        <h5 class="card-title mb-0">My Account</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a href="#settings" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                    </div>
                </div>
            </div>


            <!-- Account Content -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="card p-4">
                        <div class="text-center">
                            <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://media.tenor.com/DFfCL02_DCcAAAAM/cat-look.gif'; ?>"
                                class="rounded-circle border border-info shadow-sm" width="100" alt="Avatar">
                            <h4 class="mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <div class="text-primary fw-bold"><?php echo ucfirst($user['role']); ?></div>
                        </div>

                        <hr>

                        <div class="row text-center mb-3">
                            <div class="col-md-12">
                                <button class="btn btn-danger btn-sm me-2" onclick="showToast('Xem thông tin')"><i
                                        class="bi bi-eye-fill"></i> Xem</button>
                                <button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal"
                                    data-bs-target="#editModal"><i class="bi bi-pencil-fill"></i> Sửa</button>
                                <button class="btn btn-success btn-sm me-2" onclick="showToast('Lưu thành công')"><i
                                        class="bi bi-check-circle-fill"></i> Lưu</button>
                            </div>
                        </div>

                        <h5 class="fw-bold border-start border-4 border-success ps-2">THÔNG TIN TÀI KHOẢN</h5>
                        <div class="row mt-3 ps-4">
                            <div class="col-md-6">
                                <p><strong>Tài khoản:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                <p><strong>Họ và Tên:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                                <p><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?> <i
                                        class="bi bi-check-circle-fill text-primary"></i></p>
                                <p><strong>Vai trò:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                                <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
                                <p><strong>Hoạt động:</strong> <?php echo htmlspecialchars($user['updated_at']); ?></p>
                            </div>
                        </div>
                    </div>




                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings">
                        <div class="card p-4">
                            <div class="text-center">
                                <h4 class="mt-2">Cài đặt Tài khoản</h4>
                                <div class="text-primary fw-bold">Quản lý bảo mật & xoá tài khoản</div>
                            </div>

                            <hr>

                            <h5 class="fw-bold border-start border-4 border-success ps-2">ĐỔI MẬT KHẨU</h5>
                            <form method="POST" action="update_password.php" class="ps-4 pt-2">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password"
                                        name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                            </form>

                            <hr>

                            <h5 class="fw-bold border-start border-4 border-danger ps-2 mt-4">XOÁ TÀI KHOẢN</h5>
                            <div class="ps-4 pt-2">
                                <p class="text-muted">Một khi tài khoản bị xoá, bạn không thể khôi phục. Hãy chắc chắn.
                                </p>
                                <button class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteAccountModal">
                                    Xoá Tài khoản
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xóa Tài Khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa không? Thao tác này không thể quay lại.</p>
                    <form method="POST" action="delete_account.php">
                        <div class="mb-3">
                            <label for="confirm_delete" class="form-label">Nhập "delete" để xóa</label>
                            <input type="text" class="form-control" id="confirm_delete" name="confirm_delete" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xóa Tài Khoản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal chỉnh sửa -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border border-success rounded shadow-sm">
                <form method="POST" action="update_user.php">
                    <div class="modal-header bg-success text-white rounded-top">
                        <h5 class="modal-title" id="editModalLabel">Chỉnh sửa thông tin</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Các input giữ nguyên -->
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" name="full_name"
                                value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone"
                                value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" name="address"
                                value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="update_profile" class="btn btn-success">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function showToast(message) {
            const toastEl = document.getElementById("liveToast");
            const toastBody = document.getElementById("toastMessage");
            toastBody.textContent = message;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>

</body>

</html>