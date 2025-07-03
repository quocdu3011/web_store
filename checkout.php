<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Vui lòng đăng nhập để thanh toán';
    header('Location: login.php');
    exit;
}

if (!isset($_GET['products']) || empty($_GET['products'])) {
    header('Location: cart.php');
    exit;
}

$product_ids = explode(',', $_GET['products']);
$cart_items = [];
$subtotal = 0;
$shipping_fee_setting = get_setting('shipping_fee');
$shipping_cost = is_numeric($shipping_fee_setting) ? floatval($shipping_fee_setting) : 30000;

foreach ($product_ids as $product_id) {
    $product_id = intval($product_id);
    if (isset($_SESSION['cart'][$product_id])) {
        $product = get_product($product_id);
        if ($product) {
            $product['quantity'] = $_SESSION['cart'][$product_id];
            $product['price_display'] = get_current_price($product);
            $product['subtotal'] = $product['price_display'] * $product['quantity'];
            $cart_items[] = $product;
            $subtotal += $product['subtotal'];
        }
    }
}
$total = $subtotal + $shipping_cost;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    if (empty($shipping_address) || empty($payment_method) || empty($phone_number)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, shipping_cost, total, shipping_address, phone_number, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("idddsss", $_SESSION['user_id'], $subtotal, $shipping_cost, $total, $shipping_address, $phone_number, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price_display']);
                $stmt->execute();
                $new_stock = $item['stock'] - $item['quantity'];
                $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update_stock->bind_param("ii", $new_stock, $item['id']);
                $update_stock->execute();

                unset($_SESSION['cart'][$item['id']]);
            }

            $conn->commit();
            header("Location: order_confirmation.php?id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác Nhận Thanh Toán</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .checkout-card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .checkout-title {
      font-size: 1.5rem;
      font-weight: 600;
    }
    .form-control:focus {
      box-shadow: none;
      border-color: #dc3545;
    }
    .btn-submit {
      background-color: #dc3545;
      border: none;
      padding: 10px 30px;
    }
    .btn-submit:hover {
      background-color: #bb2d3b;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>
<div class="container my-5">
  <h2 class="mb-4">Thông Tin Thanh Toán</h2>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <div class="row">
    <div class="col-md-7">
      <div class="card checkout-card p-4">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input type="text" class="form-control" value="<?php echo sanitize_output($user['full_name']); ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?php echo sanitize_output($user['email']); ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Số điện thoại *</label>
            <input type="text" name="phone_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Địa chỉ nhận hàng *</label>
            <textarea name="shipping_address" rows="3" class="form-control" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Phương thức thanh toán *</label><br>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" required>
              <label class="form-check-label" for="cod">Thanh toán khi nhận hàng</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer">
              <label class="form-check-label" for="bank">Chuyển khoản ngân hàng</label>
            </div>
          </div>
          <button type="submit" class="btn btn-submit">Đặt hàng</button>
        </form>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card checkout-card p-4">
        <h5 class="checkout-title mb-3">Tóm Tắt Đơn Hàng</h5>
        <?php foreach ($cart_items as $item): ?>
          <div class="d-flex justify-content-between mb-2">
            <span><?php echo sanitize_output($item['name']); ?> × <?php echo $item['quantity']; ?></span>
            <span><?php echo format_price($item['subtotal']); ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between mb-2">
          <span>Tạm tính</span>
          <span><?php echo format_price($subtotal); ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Phí vận chuyển</span>
          <span><?php echo format_price($shipping_cost); ?></span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold">
          <span>Tổng cộng</span>
          <span><?php echo format_price($total); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>