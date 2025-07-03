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
    <style>
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            height: 15px;
            width: 15px;
            background-color:rgb(213, 213, 213);
            border-radius: 1px;
            cursor: pointer;
            display: flex;
            outline: none;
        }
        .form-check-label {
            color: #4c4c4c;
            font-size: 14px;
            font-family: "Poppins", sans-serif;
            cursor: pointer;
        }
        input[type="checkbox"]:after {
            font-family: "Font Awesome 6 Free";
            content: "\f00c";
            font-weight: 900;
            font-size: 12px;
            color: #fff;
            display: none;
        }
        .product-checkbox{
            margin-left: 10px;
        }
        input[type="checkbox"]:hover {
            background-color: #a5a5a5;
        }
        input[type="checkbox"]:checked {
            background-color: #0083e8;
        }
        

    </style>
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
                        <h5>Chọn tất cả</h5>
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
                        <div class="card sticky-top" style="top: 1px;" id="order-summary">
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
            document.querySelectorAll('.cart-item').forEach(function (cartItem) {
                const decrementBtn = cartItem.querySelector('.decrement');
                const incrementBtn = cartItem.querySelector('.increment');
                const quantityInput = cartItem.querySelector('.quantity-input');
                const removeBtn = cartItem.querySelector('.remove-from-cart');
                const productId = cartItem.dataset.productId;

                let pricePerItem = parseInt(
                    cartItem.querySelector('.item-subtotal').textContent.replace(/\D/g, '')
                );
                if (quantityInput) {
                    pricePerItem = pricePerItem / parseInt(quantityInput.value);
                }

                function recalcSummary() {
                    let subtotal = 0;
                    document.querySelectorAll('.cart-item').forEach(function (item) {
                        const itemSubtotal = parseInt(
                            item.querySelector('.item-subtotal').textContent.replace(/\D/g, '')
                        );
                        subtotal += itemSubtotal;
                    });
                    document.querySelector('.cart-subtotal').textContent = subtotal.toLocaleString('vi-VN') + '₫';
                    const shipping = parseInt(document.querySelector('.cart-shipping').textContent.replace(/\D/g, ''));
                    const total = subtotal + shipping;
                    document.querySelector('.cart-total').textContent = total.toLocaleString('vi-VN') + '₫';
                }

                if (decrementBtn && quantityInput) {
                    decrementBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = parseInt(quantityInput.value);
                        if (val > 1) {
                            quantityInput.value = val - 1;
                            quantityInput.dispatchEvent(new Event('change'));
                        }
                    });
                }

                if (incrementBtn && quantityInput) {
                    incrementBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = parseInt(quantityInput.value);
                        quantityInput.value = val + 1;
                        quantityInput.dispatchEvent(new Event('change'));
                    });
                }

                if (quantityInput) {
                    quantityInput.addEventListener('change', function () {
                        if (parseInt(quantityInput.value) < 1) quantityInput.value = 1;
                        const newQty = parseInt(quantityInput.value);
                        const newSubtotal = pricePerItem * newQty;
                        cartItem.querySelector('.item-subtotal').textContent = newSubtotal.toLocaleString('vi-VN') + '₫';
                        recalcSummary();
                    });
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        fetch('includes/remove_from_cart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `product_id=${productId}`
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.login) {
                                    window.location.href = 'login.php';
                                    return;
                                }
                                if (data.success) {
                                    cartItem.remove();
                                    recalcSummary();
                                    updateCartCount(data.cartCount || 0);
                                    if (document.querySelectorAll('.cart-item').length === 0) {
                                        window.location.reload();
                                    }
                                    showToast('Sản phẩm đã được xóa khỏi giỏ hàng.', 'success');
                                } else {
                                    showToast('Có lỗi xảy ra khi xóa sản phẩm khỏi giỏ hàng.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error removing from cart:', error);
                            });
                    });
                }

            });

            // chọn tất cả checkbox và cập nhật tổng tiền
            const selectAllCheckbox = document.getElementById('select-all');
            const productCheckboxes = document.querySelectorAll('.product-checkbox');

            function updateCartSummary() {
                let subtotal = 0;
                productCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        subtotal += parseInt(cb.getAttribute('data-subtotal'));
                    }
                });

                const shipping = parseInt(document.querySelector('.cart-shipping').textContent.replace(/\D/g, ''));
                const total = subtotal > 0 ? subtotal + shipping : 0;

                document.querySelector('.cart-subtotal').textContent = subtotal.toLocaleString('vi-VN') + '₫';
                document.querySelector('.cart-total').textContent = total.toLocaleString('vi-VN') + '₫';
            }

            selectAllCheckbox.addEventListener('change', function () {
                productCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                updateCartSummary();
            });

            productCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    const allChecked = Array.from(productCheckboxes).every(checkbox => checkbox.checked);
                    selectAllCheckbox.checked = allChecked;
                    updateCartSummary();
                });
            });

            // Đảm bảo tổng khởi tạo = 0 nếu chưa chọn
            updateCartSummary();

        </script>

</body>

</html>