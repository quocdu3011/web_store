<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to continue with checkout';
    header('Location: login.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get cart items
$cart_items = [];
$subtotal = 0;
$shipping_fee_setting = get_setting('shipping_fee');
$shipping_cost = is_numeric($shipping_fee_setting) ? floatval($shipping_fee_setting) : 30000;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = get_product($product_id);
    if ($product) {
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $cart_items[] = $product;
        $subtotal += $product['subtotal'];
    }
}

$total = $subtotal + $shipping_cost;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    
    if (empty($shipping_address) || empty($payment_method) || empty($phone_number)) {
        $_SESSION['error'] = 'Please fill in all required fields';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, shipping_cost, total, shipping_address, phone_number, payment_method, payment_status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("idddsss", $_SESSION['user_id'], $subtotal, $shipping_cost, $total, $shipping_address, $phone_number, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
                
                // Update product stock
                $new_stock = $item['stock'] - $item['quantity'];
                $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update_stock->bind_param("ii", $new_stock, $item['id']);
                $update_stock->execute();
            }
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?id=" . $order_id);
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = 'Failed to process order: ' . $e->getMessage();
        }
    }
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <h2 class="mb-4">Checkout</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Checkout Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST" action="">
                            <h5 class="card-title mb-4">Shipping Information</h5>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" value="<?php echo sanitize_output($user['full_name']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo sanitize_output($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Số Điện Thoại*</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address*</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                            </div>

                            <h5 class="card-title mb-4 mt-5">Payment Method</h5>
                            
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" required>
                                    <label class="form-check-label" for="cod">
                                        Cash on Delivery
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                    <label class="form-check-label" for="bank_transfer">
                                        Bank Transfer
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo sanitize_output($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                <span><?php echo format_price($item['subtotal']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo format_price($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span><?php echo format_price($shipping_cost); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total</strong>
                            <strong><?php echo format_price($total); ?></strong>
                        </div>
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