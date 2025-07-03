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
                            <div class="col-md-4 mb-4">
                            <div class="card h-100 d-flex flex-column">
                                <img src="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? $product['image'] : 'uploads/' . $product['image']; ?>"
                                    class="card-img-top" alt="<?php echo $product['name']; ?>">
                                <div class="card-body d-flex flex-column flex-grow-1">
                                    <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                    <p class="card-text text-truncate"><?php echo $product['description']; ?></p>

                                    <div class="ratingresult mb-2">
                                        <?php echo get_rating_stars($product['rating'], $product['rating_count']); ?>
                                        <span class="text-muted">(<?php echo $product['rating_count']; ?> đánh giá)</span>
                                    </div>

                                    <!-- Giá -->
                                    <div class="d-flex flex-column mt-auto mb-3">
                                        <?php
                                        $current_price = get_current_price($product);
                                        $has_discount = $current_price < $product['price'];
                                        ?>
                                        <div class="d-flex align-items-center">
                                            <p class="text-danger fw-bold mb-0">
                                                <?php echo number_format($current_price, 0, ',', '.'); ?>₫
                                            </p>
                                            <?php if ($has_discount): ?>
                                                <p class="text-muted mb-0 ms-2">
                                                    <del><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</del>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Icon Chi tiết và Thêm vào giỏ -->
                                    <div class="d-flex justify-content-between">
                                        <a href="product.php?slug=<?php echo $product['slug']; ?>"
                                            class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center shadow"
                                            style="width: 44px; height: 44px;" title="Chi tiết">
                                            <i class="fas fa-info"></i>
                                        </a>

                                        <?php if ($product['stock'] > 0): ?>
                                            <button
                                                class="btn rounded-circle d-flex align-items-center justify-content-center shadow add-to-cart"
                                                style="width: 44px; height: 44px; background-color: #dc3545;"
                                                data-product-id="<?php echo $product['id']; ?>" title="Thêm vào giỏ">
                                                <i class="fas fa-cart-plus" style="color: #fff;"></i>
                                            </button>
                                        <?php else: ?>
                                            <button
                                                class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center shadow"
                                                style="width: 44px; height: 44px;" disabled title="Hết hàng">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Badge -->
                                <?php if ($product['is_new']): ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-success">Mới</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($has_discount): ?>
                                    <div class="position-absolute top-0 start-0 p-2">
                                        <span class="badge bg-danger">Giảm <?php echo get_discounted_price($product) ?>%</span>
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