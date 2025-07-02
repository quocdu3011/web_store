<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// If query is empty, redirect to homepage
if (empty($query)) {
    header('Location: index.php');
    exit;
}

// Search for products
$products = search_products($query, 24); // Show up to 24 results
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?php echo sanitize_output($query); ?> - Cửa Hàng Công Nghệ</title>
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
                <h1 class="mb-4">Kết quả tìm kiếm: "<?php echo sanitize_output($query); ?>"</h1>
                
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <p class="mb-0">Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo sanitize_output($query); ?>".</p>
                        <p class="mb-0 mt-2">Gợi ý:</p>
                        <ul class="mb-0">
                            <li>Kiểm tra lại chính tả</li>
                            <li>Sử dụng các từ khóa khác</li>
                            <li>Sử dụng các từ khóa ngắn hơn</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Tìm thấy <?php echo count($products); ?> sản phẩm phù hợp.</p>
                    
                    <div class="row" id="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <img src="uploads/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                        <p class="card-text text-truncate"><?php echo $product['description']; ?></p>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <!-- Search Autocomplete JS -->
    <script src="assets/js/search-autocomplete.js"></script>
</body>
</html> 