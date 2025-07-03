<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID
$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// If order not found or doesn't belong to user, redirect
if (!$order) {
    header('Location: account.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đơn Hàng - Cửa Hàng Công Nghệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h2 class="my-4">Cảm Ơn Bạn Đã Đặt Hàng!</h2>
                        <p class="lead mb-4">Đơn hàng #<?php echo $order_id; ?> của bạn đã được đặt thành công.</p>

                        <div class="alert alert-info">
                            <p class="mb-0">
                                <?php if ($order['payment_method'] == 'bank_transfer'): ?>
                                    Vui lòng hoàn tất thanh toán bằng thông tin ngân hàng dưới đây:
                                    <br><br>
                                    Ngân hàng: MB Bank<br>
                                    Tên tài khoản: Trinh Quoc Du<br>
                                    Số tài khoản: 1890130112004<br>
                                    Nội dung chuyển khoản: donhang<?php echo $order_id; ?>
                                <?php else: ?>
                                    Đơn hàng sẽ được giao đến địa chỉ đã cung cấp. Thanh toán khi nhận hàng.
                                <?php endif; ?>
                            </p>
                            <?php if ($order['payment_method'] == 'bank_transfer'): ?>
                                <div class="my-3">
                                    <strong>Quét mã QR để chuyển khoản nhanh:</strong><br>
                                    <img width="250"
                                        src="https://api.vietqr.io/image/970422-1890130112004-LHneKqI.jpg?accountName=TRINH%20QUOC%20DU&amount=<?php echo (int) $order['total']; ?>&addInfo=donhang<?php echo $order_id; ?>"
                                        alt="Mã QR VietQR">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-details mt-4">
                            <h5>Chi Tiết Đơn Hàng</h5>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="text-start">Mã đơn hàng:</td>
                                        <td class="text-end">#<?php echo $order_id; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-start">Ngày đặt:</td>
                                        <td class="text-end">
                                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-start">Tổng tiền:</td>
                                        <td class="text-end"><?php echo format_price($order['total']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-start">Phương thức thanh toán:</td>
                                        <td class="text-end"><?php
                                        $payment_methods = [
                                            'bank_transfer' => 'Chuyển khoản ngân hàng',
                                            'cod' => 'Thanh toán khi nhận hàng'
                                        ];
                                        echo $payment_methods[$order['payment_method']] ?? ucwords(str_replace('_', ' ', $order['payment_method']));
                                        ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="shipping-address mt-4">
                            <h5>Thông Tin Giao Hàng</h5>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="text-start">Địa chỉ:</td>
                                        <td class="text-end">
                                            <?php echo nl2br(sanitize_output($order['shipping_address'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-start">Số điện thoại:</td>
                                        <td class="text-end"><?php echo sanitize_output($order['phone_number']); ?></td>
                                    </tr>
                                    <?php if (!empty($order['delivery_notes'])): ?>
                                        <tr>
                                            <td class="text-start">Ghi chú giao hàng:</td>
                                            <td class="text-end">
                                                <?php echo nl2br(sanitize_output($order['delivery_notes'])); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <a href="account.php" class="btn btn-primary">Xem Lịch Sử Đơn Hàng</a>
                            <a href="index.php" class="btn btn-outline-primary">Tiếp Tục Mua Sắm</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        <?php if ($order['payment_status'] !== 'paid' && $order['payment_method'] === 'bank_transfer'): ?>
            setInterval(() => {
                fetch("includes/check_payment_status.php?id=<?php echo $order_id; ?>")
                    .then(res => res.json())
                    .then(data => {
                        if (data.paid) {
                            window.location.href = "payment_success.php?id=<?php echo $order_id; ?>"; // chuyển đến trang chi tiết đơn hàng
                        }
                    });
            }, 5000); // kiểm tra mỗi 5 giây
        <?php endif; ?>
    </script>


    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>