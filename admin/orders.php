<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin or staff
if (!is_admin() && !is_staff()) {
    header('Location: ../login.php');
    exit;
}

// Handle status update
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_status') {
        $order_id = $_POST['order_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (empty($order_id) || empty($status)) {
            $error = 'Vui lòng cung cấp đầy đủ thông tin';
        } else {
            if (update_order_status($order_id, $status, $notes)) {
                $message = 'Cập nhật trạng thái đơn hàng thành công';
            } else {
                $error = 'Không thể cập nhật trạng thái đơn hàng';
            }
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

// Get orders
$orders = get_orders($status_filter);

// Get order details if viewing a specific order
$order = null;
$order_items = [];
$order_history = [];
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $order = get_order($order_id);
    if ($order) {
        $order_items = get_order_items($order_id);
        $order_history = get_order_history($order_id);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Quản Trị</title>
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
                <?php if ($order): ?>
                    <!-- Order Detail View -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h1>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Mã đơn hàng:</th>
                                            <td>#<?php echo $order['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày đặt:</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái:</th>
                                            <td>
                                                <span class="badge bg-<?php echo get_order_status_class($order['status']); ?>">
                                                    <?php echo get_order_status_text($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Thanh toán:</th>
                                            <td>
                                                <span class="badge bg-<?php echo ($order['payment_status'] == 'paid') ? 'success' : (($order['payment_status'] == 'failed') ? 'danger' : 'warning'); ?>">
                                                    <?php echo ($order['payment_status'] == 'paid') ? 'Đã thanh toán' : (($order['payment_status'] == 'failed') ? 'Thất bại' : 'Chờ thanh toán'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Phương thức:</th>
                                            <td><?php echo $order['payment_method']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tổng tiền:</th>
                                            <td class="fw-bold text-danger"><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông tin khách hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Khách hàng:</th>
                                            <td><?php echo sanitize_output($order['full_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo sanitize_output($order['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Số điện thoại:</th>
                                            <td><?php echo sanitize_output($order['phone_number']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Địa chỉ:</th>
                                            <td><?php echo nl2br(sanitize_output($order['shipping_address'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ghi chú:</th>
                                            <td><?php echo nl2br(sanitize_output($order['delivery_notes'] ?: 'Không có')); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Sản phẩm đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>Đơn giá</th>
                                            <th>Số lượng</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['image']): ?>
                                                            <img src="../uploads/<?php echo sanitize_output($item['image']); ?>" alt="<?php echo sanitize_output($item['name']); ?>" width="50" class="me-2">
                                                        <?php endif; ?>
                                                        <div>
                                                            <div><?php echo sanitize_output($item['name']); ?></div>
                                                            <small class="text-muted">ID: <?php echo $item['product_id']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end">Tạm tính:</td>
                                            <td><?php echo number_format($order['subtotal'], 0, ',', '.'); ?>₫</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end">Phí vận chuyển:</td>
                                            <td><?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?>₫</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                            <td class="fw-bold text-danger"><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Lịch sử đơn hàng</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($order_history)): ?>
                                        <p class="text-muted">Chưa có lịch sử đơn hàng.</p>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($order_history as $history): ?>
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="badge bg-<?php echo get_order_status_class($history['status']); ?> me-2">
                                                                <?php echo get_order_status_text($history['status']); ?>
                                                            </span>
                                                            <small>bởi <?php echo sanitize_output($history['full_name'] ?: $history['username']); ?> (<?php echo $history['role']; ?>)</small>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></small>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($history['notes'])): ?>
                                                        <div class="mt-2 small"><?php echo nl2br(sanitize_output($history['notes'])); ?></div>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Cập nhật trạng thái</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Trạng thái mới</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="">-- Chọn trạng thái --</option>
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Ghi chú</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Orders List View -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1>Quản Lý Đơn Hàng</h1>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <div class="btn-group">
                            <a href="orders.php" class="btn btn-outline-primary <?php echo $status_filter === null ? 'active' : ''; ?>">Tất cả</a>
                            <a href="orders.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Chờ xử lý</a>
                            <a href="orders.php?status=processing" class="btn btn-outline-info <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">Đang xử lý</a>
                            <a href="orders.php?status=completed" class="btn btn-outline-success <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Hoàn thành</a>
                            <a href="orders.php?status=cancelled" class="btn btn-outline-danger <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Đã hủy</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>SĐT</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Không có đơn hàng nào.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo sanitize_output($order['full_name']); ?></td>
                                            <td><?php echo sanitize_output($order['phone_number']); ?></td>
                                            <td><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</td>
                                            <td>
                                                <span class="badge bg-<?php echo ($order['payment_status'] == 'paid') ? 'success' : (($order['payment_status'] == 'failed') ? 'danger' : 'warning'); ?>">
                                                    <?php echo ($order['payment_status'] == 'paid') ? 'Đã thanh toán' : (($order['payment_status'] == 'failed') ? 'Thất bại' : 'Chờ thanh toán'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo get_order_status_class($order['status']); ?>">
                                                    <?php echo get_order_status_text($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 