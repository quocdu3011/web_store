<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$cart_items = [];
$subtotal = 0;
$shipping_fee_setting = get_setting('shipping_fee');
$shipping_cost = is_numeric($shipping_fee_setting) ? floatval($shipping_fee_setting) : 30000;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product($product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['price_display'] = get_current_price($product);
            $product['subtotal'] = $product['price_display'] * $quantity;
            $cart_items[] = $product;
            $subtotal += $product['subtotal'];
        }
    }
}
$total = $subtotal + $shipping_cost;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Cửa Hàng Công Nghệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <h2 class="mb-4">Giỏ Hàng</h2>
        <div class="alert alert-info mb-3">
            <strong>Thông báo:</strong> Để đảm bảo quyền lợi của bạn, vui lòng không chia sẻ thông tin giỏ hàng với
            người khác.

            <?php if (empty($cart_items)): ?>
                <div class="alert alert-info">
                    Giỏ hàng của bạn đang trống. <a href="index.php">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label" for="select-all">
                        Chọn tất cả
                    </label>
                </div>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-md-8">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="card mb-3 cart-item" data-product-id="<?php echo $item['id']; ?>">
                                <div class="row g-0">
                                    <div class="col-md-3 d-flex align-items-center">
                                        <!-- Ô checkbox -->
                                        <input class="form-check-input me-2 product-checkbox" type="checkbox"
                                            data-product-id="<?php echo $item['id']; ?>"
                                            data-subtotal="<?php echo $item['subtotal']; ?>">
                                        <!-- Ảnh sản phẩm -->
                                        <img src="<?php echo $item['image'] ? sanitize_output($item['image']) : 'assets/images/no-image.jpg'; ?>"
                                            class="img-fluid rounded-start" alt="<?php echo sanitize_output($item['name']); ?>">
                                    </div>

                                    <div class="col-md-9">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="card-title"><?php echo sanitize_output($item['name']); ?></h5>
                                                <button type="button" class="btn-close remove-from-cart"
                                                    data-product-id="<?php echo $item['id']; ?>"></button>
                                            </div>
                                            <p class="card-text text-muted">
                                                <?php echo sanitize_output($item['manufacturer']); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="quantity-control">
                                                    <!-- <button class="btn btn-outline-secondary decrement" type="button">-</button> -->
                                                    <!-- <input type="number" class="form-control d-inline-block mx-2 quantity-input" 
                                                       style="width: 60px;" value="<?php echo $item['quantity']; ?>"
                                                       min="1" data-product-id="<?php echo $item['id']; ?>"> -->
                                                    <label for="quantity-input" class="quantity-label">Số lượng</label>
                                                    <label><?php echo $item['quantity']; ?></label>
                                                    <!-- <button class="btn btn-outline-secondary increment" type="button">+</button> -->
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold item-subtotal">
                                                        <?php echo number_format($item['subtotal'], 0, ',', '.') . '₫'; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo number_format($item['price_display'], 0, ',', '.') . '₫'; ?>
                                                        /
                                                        sản phẩm
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Tóm Tắt Đơn Hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Tạm tính</span>
                                    <span
                                        class="cart-subtotal"><?php echo number_format($subtotal, 0, ',', '.') . '₫'; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Phí vận chuyển</span>
                                    <span
                                        class="cart-shipping"><?php echo number_format($shipping_cost, 0, ',', '.') . '₫'; ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Tổng cộng</strong>
                                    <strong
                                        class="cart-total"><?php echo number_format($total, 0, ',', '.') . '₫'; ?></strong>
                                </div>
                                <button class="btn btn-primary w-100" onclick="window.location.href='checkout.php'">
                                    Tiến hành đặt hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php include 'includes/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/main.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectAll = document.getElementById('select-all');
                const productCheckboxes = document.querySelectorAll('.product-checkbox');
                const subtotalSpan = document.querySelector('.cart-subtotal');
                const shippingSpan = document.querySelector('.cart-shipping');
                const totalSpan = document.querySelector('.cart-total');

                function updateSummary() {
                    let subtotal = 0;
                    productCheckboxes.forEach(function (checkbox) {
                        if (checkbox.checked) {
                            subtotal += parseInt(checkbox.getAttribute('data-subtotal'));
                        }
                    });
                    subtotalSpan.textContent = subtotal.toLocaleString('vi-VN') + '₫';
                    let shipping = subtotal > 0 ? parseInt(shippingSpan.textContent.replace(/\D/g, '')) : 0;
                    totalSpan.textContent = (subtotal + shipping).toLocaleString('vi-VN') + '₫';
                }

                selectAll.addEventListener('change', function () {
                    productCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    updateSummary();
                });

                productCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
                        selectAll.checked = allChecked;
                        updateSummary();
                    });
                });

                // Mặc định: tổng = 0
                updateSummary();
            });
        </script>

</body>

</html>