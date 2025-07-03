<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Lấy đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Không tìm thấy đơn hàng.</div></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết đơn hàng #<?php echo $order['id']; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .order-img { width: 70px; height: 70px; object-fit: cover; border-radius: 4px; }
    .order-label { width: 160px; color: #6c757d; font-weight: 500; }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<div class="container my-5">
  <h2 class="mb-4">📄 Chi tiết đơn hàng #<?php echo $order['id']; ?></h2>

  <!-- Thông tin đơn -->
  <div class="mb-4 border p-4 rounded shadow-sm bg-light">
    <div class="row mb-2">
      <div class="col-md-4 order-label">Ngày đặt hàng:</div>
      <div class="col-md-8"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
    </div>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Trạng thái đơn hàng:</div>
    <div class="col-md-8">
      <?php
        if ($order['status'] === 'pending') {
            echo '<span class="badge bg-warning text-dark">Đang chờ</span>';
        } elseif ($order['status'] === 'paid') {
            echo '<span class="badge bg-info text-dark">Đang giao</span>';
        } elseif ($order['status'] === 'failed') {
            echo '<span class="badge bg-danger">Đã hủy</span>';
        } else {
            echo '<span class="badge bg-secondary">' . ucfirst($order['status']) . '</span>';
        }
      ?>
    </div>
    </div>
    <div class="row mb-2">
    <div class="col-md-4 order-label">Phương thức thanh toán:</div>
    <div class="col-md-8">
      <?php
        $method = $order['payment_method'] ?? 'COD';
        if ($method === 'bank_transfer') {
          echo 'Chuyển khoản ngân hàng';
        } elseif ($method === 'COD') {
          echo 'Thanh toán khi nhận hàng';
        } else {
          echo htmlspecialchars(strtoupper($method));
        }
      ?>
    </div>
    </div>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Trạng thái thanh toán:</div>
      <div class="col-md-8">
        <?php
          if ($order['payment_status'] === 'pending') {
              echo '<span class="badge bg-warning text-dark">Đang chờ</span>';
          } elseif ($order['payment_status'] === 'paid') {
              echo '<span class="badge bg-success">Đã thanh toán</span>';
          } elseif ($order['payment_status'] === 'failed') {
              echo '<span class="badge bg-danger">Không thành công</span>';
          } else {
              echo '<span class="badge bg-secondary">' . ucfirst($order['payment_status']) . '</span>';
          }
        ?>
      </div>
    </div>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Phí vận chuyển:</div>
      <div class="col-md-8"><?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?>₫</div>
    </div>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Tổng thanh toán:</div>
      <div class="col-md-8 fw-bold text-danger"><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</div>
    </div>
  </div>

  <!-- Sản phẩm -->
  <h5 class="mb-3">🛍 Sản phẩm</h5>
  <div class="list-group mb-4">
    <?php
    $stmt_items = $conn->prepare("
        SELECT oi.*, p.name, p.slug, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($items as $item):
    ?>
    <div class="list-group-item d-flex align-items-center">
      <img src="<?php echo filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : 'uploads/' . $item['image']; ?>"
           alt="<?php echo $item['name']; ?>" class="me-3 order-img">
      <div class="flex-grow-1">
        <a href="product.php?slug=<?php echo $item['slug']; ?>" class="text-decoration-none fw-semibold text-dark">
          <?php echo $item['name']; ?>
        </a>
        <div class="text-muted">Số lượng: <?php echo $item['quantity']; ?></div>
      </div>
      <div class="fw-bold text-danger"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Thông tin giao hàng -->
  <h5 class="mb-3">🚚 Giao hàng</h5>
  <div class="border p-4 rounded shadow-sm bg-light">
    <div class="row mb-2">
      <div class="col-md-4 order-label">Số điện thoại:</div>
      <div class="col-md-8"><?php echo $order['phone_number']; ?></div>
    </div>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Địa chỉ:</div>
      <div class="col-md-8"><?php echo $order['shipping_address']; ?></div>
    </div>
    <?php if (!empty($order['delivery_notes'])): ?>
    <div class="row mb-2">
      <div class="col-md-4 order-label">Ghi chú:</div>
      <div class="col-md-8"><?php echo nl2br($order['delivery_notes']); ?></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Nút quay lại -->
  <div class="mt-4 text-end">
    <a href="my_orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
