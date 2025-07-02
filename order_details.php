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
    <title>Order Details - Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
                        <li class="breadcrumb-item active">Order #<?php echo $order_id; ?></li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Order Details</h5>
                            <span class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p class="mb-1">Order ID: #<?php echo $order_id; ?></p>
                                <p class="mb-1">Date: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1">Email: <?php echo sanitize_output($order['email']); ?></p>
                                <p class="mb-1">Status: <?php echo ucfirst($order['status']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Shipping Address</h6>
                                <p class="mb-1"><?php echo sanitize_output($order['shipping_address']); ?></p>
                            </div>
                        </div>

                        <h6>Order Items</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $item['image'] ? 'uploads/' . sanitize_output($item['image']) : 'assets/images/no-image.jpg'; ?>"
                                                         alt="<?php echo sanitize_output($item['name']); ?>"
                                                         class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php echo sanitize_output($item['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo format_price($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end"><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><?php echo format_price($order['subtotal']); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                        <td class="text-end"><?php echo format_price($order['shipping_cost']); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong><?php echo format_price($order['total']); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
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