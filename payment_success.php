<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra nếu không có ID thì chuyển hướng
$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Truy vấn đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order || $order['payment_status'] !== 'paid') {
    header('Location: my_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thanh toán thành công</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .success-icon {
      font-size: 4rem;
      color: #28a745;
    }
    .thank-you {
      font-size: 1.5rem;
      font-weight: 500;
    }
    .btn-group .btn {
      min-width: 160px;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8 text-center">
      <i class="fas fa-check-circle success-icon mb-3"></i>
      <h2 class="mb-3 text-success">Thanh Toán Thành Công</h2>
      <p class="thank-you mb-4">Cảm ơn bạn! Đơn hàng #<?php echo $order['id']; ?> đã được xác nhận thanh toán.</p>

      <div class="card mx-auto shadow-sm" style="max-width: 400px;">
        <div class="card-body text-start">
          <p><strong>🧾 Mã đơn hàng:</strong> #<?php echo $order['id']; ?></p>
          <p><strong>💰 Số tiền:</strong> <?php echo format_price($order['total']); ?></p>
          <p><strong>📅 Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
          <p><strong>🔒 Trạng thái thanh toán:</strong> <span class="badge bg-success">Đã thanh toán</span></p>
        </div>
      </div>

      <div class="btn-group mt-4" role="group">
        <a href="my_orders.php" class="btn btn-outline-primary">
          <i class="fas fa-list"></i> Xem đơn hàng
        </a>
        <a href="index.php" class="btn btn-primary">
          <i class="fas fa-home"></i> Trang chủ
        </a>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
