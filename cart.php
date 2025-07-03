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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giỏ Hàng - Cửa Hàng Công Nghệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .cart-item-card {
            transition: box-shadow 0.3s;
        }

        .cart-item-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .custom-checkbox {
            appearance: none;
            width: 24px;
            height: 24px;
            border: 2px solid #999;
            border-radius: 6px;
            position: relative;
            cursor: pointer;
            margin-right: 10px;
        }

        .custom-checkbox:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .custom-checkbox:checked::after {
            content: '\2713';
            position: absolute;
            top: 1px;
            left: 6px;
            color: #fff;
            font-size: 16px;
        }

        #bottom-summary-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 95%;
            max-width: 600px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            z-index: 1050;
            font-size: 1rem;
        }

        #bottom-summary-bar .summary-price {
            font-weight: 500;
            color: #333;
        }

        #bottom-summary-bar .btn-buy {
            background-color: #dc3545;
            color: #fff;
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            transition: background-color 0.2s ease-in-out;
        }

        #bottom-summary-bar .btn-buy:hover {
            background-color: #bb2d3b;
        }

        @media (max-width: 576px) {
            #bottom-summary-bar {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <h2 class="mb-4">Giỏ Hàng</h2>
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Giỏ hàng của bạn đang trống. <a href="index.php">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="form-check mb-3">
                <input class="custom-checkbox" type="checkbox" id="select-all" checked>
                <label class="form-check-label" for="select-all">
                    <strong>Chọn tất cả</strong>
                </label>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="card mb-3 cart-item cart-item-card d-flex flex-column flex-md-row align-items-center"
                            data-product-id="<?php echo $item['id']; ?>">
                            <input width="24px" height="24px" class="custom-checkbox m-3 product-checkbox" type="checkbox"
                                data-product-id="<?php echo $item['id']; ?>" data-subtotal="<?php echo $item['subtotal']; ?>"
                                checked>
                            <img src="<?php echo sanitize_output($item['image'] ?: 'assets/images/no-image.jpg'); ?>"
                                class="img-fluid rounded-start mx-3" style="width: 120px; height: auto;"
                                alt="<?php echo sanitize_output($item['name']); ?>">
                            <div class="card-body w-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1"><?php echo sanitize_output($item['name']); ?></h5>
                                    <button class="btn-close remove-from-cart"
                                        data-product-id="<?php echo $item['id']; ?>"></button>
                                </div>
                                <p class="card-text text-muted mb-1"><?php echo sanitize_output($item['manufacturer']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Số lượng: <?php echo $item['quantity']; ?></span>
                                    <div class="text-end">
                                        <div class="fw-bold item-subtotal">
                                            <?php echo number_format($item['subtotal'], 0, ',', '.') . '₫'; ?></div>
                                        <small
                                            class="text-muted"><?php echo number_format($item['price_display'], 0, ',', '.') . '₫ / sản phẩm'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="bottom-summary-bar">
                <div class="summary-price">
                    Tạm tính: <span class="cart-subtotal"><?php echo number_format($subtotal, 0, ',', '.') . '₫'; ?></span>
                </div>
                <button type="button" class="btn-buy" id="buy-selected">Mua ngay</button>
            </div>
        <?php endif; ?>
    </div>

    <?php //include 'includes/footer.php'; ?>
    <script>
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        const buyButton = document.getElementById('buy-selected');

        function updateCartSummary() {
            let subtotal = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    subtotal += parseInt(cb.getAttribute('data-subtotal'));
                }
            });
            document.querySelector('.cart-subtotal').textContent = subtotal.toLocaleString('vi-VN') + '₫';
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCartSummary);
        });

        selectAllCheckbox.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateCartSummary();
        });

        updateCartSummary();

        buyButton.addEventListener('click', function () {
            const selectedIds = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.productId);

            if (selectedIds.length === 0) {
                alert("Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.");
                return;
            }

            // Chuyển tới trang checkout.php và truyền danh sách id qua URL
            const url = `checkout.php?products=${selectedIds.join(',')}`;
            window.location.href = url;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>

        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');

        function updateCartSummary() {
            let subtotal = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    subtotal += parseInt(cb.getAttribute('data-subtotal'));
                }
            });
            document.querySelector('.cart-subtotal').textContent = subtotal.toLocaleString('vi-VN') + '₫';
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCartSummary);
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCartSummary();
            });
        }

        updateCartSummary();

        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                fetch('includes/remove_from_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${productId}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`.cart-item[data-product-id="${productId}"]`).remove();
                            document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cartCount || 0);
                            updateCartSummary();
                        }
                    });
            });
        });
    </script>
</body>

</html>