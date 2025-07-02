<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin() && !is_staff()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$stats = [
    'products' => 0,
    'orders' => 0,
    'users' => 0,
    'revenue' => 0
];

// Count products
$result = $conn->query("SELECT COUNT(*) AS count FROM products");
if ($result) {
    $stats['products'] = $result->fetch_assoc()['count'];
}

// Count orders
$result = $conn->query("SELECT COUNT(*) AS count FROM orders");
if ($result) {
    $stats['orders'] = $result->fetch_assoc()['count'];
}

// Count users
$result = $conn->query("SELECT COUNT(*) AS count FROM users");
if ($result) {
    $stats['users'] = $result->fetch_assoc()['count'];
}

// Calculate total revenue
$result = $conn->query("SELECT SUM(total) AS revenue FROM orders WHERE status = 'completed'");
if ($result) {
    $stats['revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
}

// Get recent orders
$recent_orders = [];
$result = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// Get low stock products
$low_stock_products = [];
$result = $conn->query("SELECT * FROM products WHERE stock <= 5 AND stock > 0 ORDER BY stock ASC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $low_stock_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển - Quản Trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1>Bảng Điều Khiển</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Sản Phẩm</h5>
                                        <h2 class="card-text"><?php echo $stats['products']; ?></h2>
                                    </div>
                                    <i class="fas fa-box fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="products.php" class="text-white text-decoration-none">Xem chi tiết</a>
                                <i class="fas fa-arrow-circle-right"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Đơn Hàng</h5>
                                        <h2 class="card-text"><?php echo $stats['orders']; ?></h2>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="orders.php" class="text-white text-decoration-none">Xem chi tiết</a>
                                <i class="fas fa-arrow-circle-right"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Người Dùng</h5>
                                        <h2 class="card-text"><?php echo $stats['users']; ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="users.php" class="text-white text-decoration-none">Xem chi tiết</a>
                                <i class="fas fa-arrow-circle-right"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Doanh Thu</h5>
                                        <h2 class="card-text"><?php echo format_price($stats['revenue']); ?></h2>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-3x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="orders.php" class="text-white text-decoration-none">Xem chi tiết</a>
                                <i class="fas fa-arrow-circle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Đơn Hàng Gần Đây</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_orders)): ?>
                                    <p class="text-muted">Không có đơn hàng gần đây.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Khách Hàng</th>
                                                    <th>Trạng Thái</th>
                                                    <th>Tổng Tiền</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_orders as $order): ?>
                                                    <tr>
                                                        <td><?php echo $order['id']; ?></td>
                                                        <td><?php echo sanitize_output($order['username']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo get_order_status_class($order['status']); ?>">
                                                                <?php echo get_order_status_text($order['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo format_price($order['total']); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">Xem Tất Cả Đơn Hàng</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Products -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Sản Phẩm Sắp Hết Hàng</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($low_stock_products)): ?>
                                    <p class="text-muted">Không có sản phẩm nào sắp hết hàng.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Sản Phẩm</th>
                                                    <th>Tồn Kho</th>
                                                    <th>Thao Tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($low_stock_products as $product): ?>
                                                    <tr>
                                                        <td><?php echo $product['id']; ?></td>
                                                        <td><?php echo sanitize_output($product['name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-warning"><?php echo $product['stock']; ?></span>
                                                        </td>
                                                        <td>
                                                            <a href="products.php" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="products.php" class="btn btn-sm btn-outline-primary">Quản Lý Sản Phẩm</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html> 