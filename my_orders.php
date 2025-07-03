<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';

// Lấy đơn hàng theo trạng thái nếu có
$sql = "SELECT * FROM orders WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đơn hàng của tôi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/orders.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<div class="container my-5">
  <h2 class="mb-4">🧾 Đơn hàng của tôi</h2>

  <!-- Bộ lọc trạng thái -->
  <form method="get" class="mb-4">
    <div class="row g-2">
      <div class="col-md-4">
        <select name="status" class="form-select" onchange="this.form.submit()">
          <option value="">-- Tất cả trạng thái --</option>
          <?php
          $statuses = ['pending' => 'Chờ xác nhận', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
          foreach ($statuses as $key => $label) {
              $selected = ($status_filter === $key) ? 'selected' : '';
              echo "<option value='$key' $selected>$label</option>";
          }
          ?>
        </select>
      </div>
    </div>
  </form>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">Không tìm thấy đơn hàng nào.</div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div class="card mb-4 shadow-sm order-card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
          <div>
            <strong>Đơn hàng #<?php echo $order['id']; ?></strong> -
            <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
          </div>
          <span class="badge order-status-<?php echo $order['status']; ?>">
            <?php echo ucfirst($order['status']); ?>
          </span>
        </div>

        <div class="card-body">
          <!-- Hiển thị sản phẩm -->
          <?php
          $stmt_items = $conn->prepare("
              SELECT oi.*, p.name, p.slug, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?
          ");
          $stmt_items->bind_param("i", $order['id']);
          $stmt_items->execute();
          $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
          ?>

          <?php foreach ($items as $item): ?>
            <div class="d-flex border-bottom py-2 align-items-center">
              <img src="<?php echo filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : 'uploads/' . $item['image']; ?>"
                   alt="<?php echo $item['name']; ?>" class="me-3 order-img">
              <div class="flex-grow-1">
                <a href="product.php?slug=<?php echo $item['slug']; ?>" class="order-name">
                  <?php echo $item['name']; ?>
                </a>
                <div>Số lượng: <strong><?php echo $item['quantity']; ?></strong></div>
              </div>
              <div class="text-danger fw-bold">
                <?php echo number_format($item['price'], 0, ',', '.'); ?>₫
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Thông tin thanh toán -->
          <div class="mt-3">
            <?php
            $payment_method = $order['payment_method'] ?? 'COD';
            if ($payment_method === 'bank_transfer') {
                $payment_text = 'Chuyển khoản ngân hàng';
            } elseif ($payment_method === 'COD') {
                $payment_text = 'Thanh toán khi nhận hàng';
            } else {
                $payment_text = strtoupper($payment_method);
            }
            ?>
            <div>Phương thức thanh toán: <strong><?php echo $payment_text; ?></strong></div>
            <div class="text-end mt-2">
              <strong>Tổng cộng: </strong>
              <span class="text-danger fw-bold fs-5"><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</span>
            </div>
          </div>

          <!-- Hành động -->
          <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary">
              <i class="fas fa-eye"></i> Xem chi tiết
            </a>

            <?php if ($order['status'] === 'pending'): ?>
              <form action="cancel_order.php" method="post" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <button type="submit" class="btn btn-outline-danger">
                  <i class="fas fa-times-circle"></i> Hủy đơn
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
