<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Fetch featured products
$featured_query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
$featured_result = $conn->query($featured_query);
$featured_products = $featured_result->fetch_all(MYSQLI_ASSOC);

// Fetch new arrivals
$new_query = "SELECT * FROM products WHERE is_new = 1 ORDER BY created_at DESC LIMIT 8";
$new_result = $conn->query($new_query);
$new_products = $new_result->fetch_all(MYSQLI_ASSOC);

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    echo "<script>showToast('$message','success');</script>";
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Công Nghệ - Điểm đến công nghệ tin cậy của bạn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Sản Phẩm Nổi Bật</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($featured_products as $product): ?>
                            <div class="mb-3">
                                <a href="product.php?slug=<?php echo $product['slug']; ?>" class="text-decoration-none">
                                    <img src="<?php echo $product['image']; ?>" class="img-fluid mb-2" alt="<?php echo $product['name']; ?>">
                                    <h6 class="text-dark mb-1"><?php echo $product['name']; ?></h6>
                                    <p class="text-danger mb-0"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Khuyến Mãi Đặc Biệt</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-gift"></i> Giảm 10% cho đơn hàng trên 20 triệu
                        </div>
                        <div class="alert alert-success">
                            <i class="fas fa-truck"></i> Miễn phí vận chuyển nội thành
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9">
                <!-- Hero Section -->
                <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="assets/images/m4-len-doi-tang-airpods-4.png" class="d-block w-100" alt="Công Nghệ Mới Nhất">
                            <div class="carousel-caption">
                                <h3>Khám Phá Công Nghệ Mới</h3>
                                <p>Sản phẩm chính hãng với bảo hành uy tín</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="assets/images/vivo-v50-home.png" class="d-block w-100" alt="Sản Phẩm Mới">
                            <div class="carousel-caption">
                                <h3>Hàng Mới Về</h3>
                                <p>Các sản phẩm công nghệ mới nhất</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>

                <!-- New Arrivals Section -->
                <h2 class="mb-4">Sản Phẩm Mới</h2>
                <div class="row" id="products-grid">
                    <?php foreach ($new_products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 d-flex flex-column">
                                <img src="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? $product['image'] : 'uploads/' . $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                                <div class="card-body d-flex flex-column flex-grow-1">
                                    <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                    <p class="card-text text-truncate"><?php echo $product['description']; ?></p>
                                    <div class="d-flex flex-column mt-auto">
                                        <div class="d-flex flex-column">
                                            <?php 
                                            $current_price = get_current_price($product);
                                            $has_discount = $current_price < $product['price'];
                                            ?>
                                            <?php if ($has_discount): ?>
                                                <p class="mb-0">
                                                    <del class="text-muted"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</del>
                                                </p>
                                                <p class="text-danger fw-bold"><?php echo number_format($current_price, 0, ',', '.'); ?>₫</p>
                                            <?php else: ?>
                                                <p class="text-danger fw-bold"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex flex-column align-items-center">
                                            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-primary w-100 mb-2">Chi Tiết</a>
                                            <?php if ($product['stock'] > 0): ?>
                                                <button class="btn btn-success add-to-cart w-100" data-product-id="<?php echo $product['id']; ?>">
                                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100" disabled>Hết hàng</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($product['is_new']): ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-success">Mới</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($has_discount): ?>
                                    <div class="position-absolute top-0 start-0 p-2">
                                        <span class="badge bg-danger">Giảm giá</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <!-- Search Autocomplete JS -->
    <script src="assets/js/search-autocomplete.js"></script>
</body>
</html> 