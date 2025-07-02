<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    echo '<div class="alert alert-danger">Không có quyền truy cập</div>';
    exit;
}

// Get order ID
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    echo '<div class="alert alert-danger">ID đơn hàng không hợp lệ</div>';
    exit;
}

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="alert alert-danger">Không tìm thấy đơn hàng</div>';
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h6 class="fw-bold">Thông Tin Đơn Hàng</h6>
        <table class="table table-sm">
            <tr>
                <th style="width: 150px;">ID Đơn Hàng:</th>
                <td><?php echo $order['id']; ?></td>
            </tr>
            <tr>
                <th>Ngày Đặt:</th>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <th>Trạng Thái:</th>
                <td>
                    <span class="badge bg-<?php echo get_order_status_class($order['status']); ?>">
                        <?php echo get_order_status_text($order['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Phương Thức Thanh Toán:</th>
                <td><?php
                $payment_methods = [
                    'bank_transfer' => 'Chuyển khoản ngân hàng',
                    'cod' => 'Thanh toán khi nhận hàng'
                ];
                echo $payment_methods[$order['payment_method']] ?? ucwords(str_replace('_', ' ', $order['payment_method']));
                ?></td>
            </tr>
            <tr>
                <th>Trạng Thái Thanh Toán:</th>
                <td>
                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                        <?php echo $order['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="fw-bold">Thông Tin Khách Hàng</h6>
        <table class="table table-sm">
            <tr>
                <th style="width: 150px;">Tên Người Dùng:</th>
                <td><?php echo sanitize_output($order['username']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo sanitize_output($order['email']); ?></td>
            </tr>
            <tr>
                <th>Số Điện Thoại:</th>
                <td><?php echo sanitize_output($order['phone_number']); ?></td>
            </tr>
            <tr>
                <th>Địa Chỉ Giao Hàng:</th>
                <td><?php echo nl2br(sanitize_output($order['shipping_address'])); ?></td>
            </tr>
            <?php if (!empty($order['delivery_notes'])): ?>
                <tr>
                    <th>Ghi Chú:</th>
                    <td><?php echo nl2br(sanitize_output($order['delivery_notes'])); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<h6 class="fw-bold">Sản Phẩm Đã Đặt</h6>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sản Phẩm</th>
                <th>Đơn Giá</th>
                <th>Số Lượng</th>
                <th>Thành Tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if ($item['image']): ?>
                                <img src="../uploads/<?php echo sanitize_output($item['image']); ?>"
                                    alt="<?php echo sanitize_output($item['name']); ?>"
                                    style="width: 50px; height: 50px; object-fit: contain; margin-right: 10px;">
                            <?php endif; ?>
                            <span><?php echo sanitize_output($item['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo format_price($item['price']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Tạm Tính:</th>
                <td><?php echo format_price($order['subtotal']); ?></td>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Phí Giao Hàng:</th>
                <td><?php echo format_price($order['shipping_cost']); ?></td>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Tổng Cộng:</th>
                <td class="fw-bold"><?php echo format_price($order['total']); ?></td>
            </tr>
        </tfoot>
    </table>
</div>