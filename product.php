<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';



// Get product slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Get product category (child category)
$stmt = $conn->prepare("SELECT c.*, p.name as parent_name, p.slug as parent_slug FROM child_categories c LEFT JOIN parent_categories p ON c.parent_id = p.id WHERE c.id = ?");
$stmt->bind_param("i", $product['category_id']);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

// Get product specifications
$stmt = $conn->prepare("SELECT * FROM product_specs WHERE product_id = ?");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$specs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// If no additional images found, use the main product image
if (empty($images)) {
    $images = [['image_path' => $product['image'], 'is_primary' => 1]];
}

// Get related products
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? LIMIT 6");
$stmt->bind_param("i", $product['category_id']);
$stmt->execute();
$related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_SESSION['user_id'])) {
    $new_rating = (int) $_POST['rating'];
    $user_id = $_SESSION['user_id'];
    $product_id = $product['id'];

    if ($new_rating >= 1 && $new_rating <= 5) {
        // Kiểm tra xem user đã đánh giá chưa
        $checkStmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $user_id, $product_id);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $_SESSION['rating_message'] = 'Bạn đã đánh giá sản phẩm này trước đó.';
        } else {
            // Lưu đánh giá mới
            $insertStmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $user_id, $product_id, $new_rating);
            $insertStmt->execute();

            // Cập nhật sản phẩm
            $total_rating = $product['rating'] * $product['rating_count'];
            $new_count = $product['rating_count'] + 1;
            $new_average = ($total_rating + $new_rating) / $new_count;

            $updateStmt = $conn->prepare("UPDATE products SET rating = ?, rating_count = ? WHERE id = ?");
            $updateStmt->bind_param("dii", $new_average, $new_count, $product_id);
            $updateStmt->execute();

            $_SESSION['rating_message'] = 'Cảm ơn bạn đã đánh giá!';
        }

        // Reload trang để hiện thông báo
        header("Location: product.php?slug=" . urlencode($slug));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Cửa Hàng Công Nghệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    .breadcrumb-wrapper {
        padding: 10px 0;
        background-color: #fff;
    }

    .custom-breadcrumb {
        background-color: transparent;
        margin-bottom: 0;
        font-size: 14px;
        color: #212121;
        padding: 0;
    }

    .custom-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
        content: "/";
        color: #999;
        padding: 0 10px;
    }

    .custom-breadcrumb .breadcrumb-item a {
        color: #212121;
        text-decoration: none;
        font-weight: 400;
    }

    .custom-breadcrumb .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    .custom-breadcrumb .breadcrumb-item.active {
        color: #757575;
        font-weight: 400;
        pointer-events: none;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const breadcrumb = document.querySelector(".custom-breadcrumb");
        if (breadcrumb) {
            breadcrumb.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
<script>
    const stars = document.querySelectorAll('.star-icon');
    const ratingValue = document.getElementById('rating-value');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = star.getAttribute('data-value');
            ratingValue.value = value;
            highlightStars(value);
        });
    });

    function highlightStars(value) {
        stars.forEach(star => {
            const starValue = star.getAttribute('data-value');
            star.classList.remove('fa-solid');
            star.classList.add('fa-regular');
            if (starValue <= value) {
                star.classList.remove('fa-regular');
                star.classList.add('fa-solid');
            }
        });
    }
</script>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-wrapper mb-3">
            <ol class="breadcrumb custom-breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php" class="d-flex align-items-center text-dark">
                        <i class="bi bi-house-door-fill me-1"></i>Trang chủ
                    </a>
                </li>

                <?php if (!empty($category['parent_slug']) && !empty($category['parent_name'])): ?>
                    <li class="breadcrumb-item">
                        <a href="category.php?slug=<?php echo $category['parent_slug']; ?>">
                            <?php echo $category['parent_name']; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (!empty($category['slug']) && !empty($category['name'])): ?>
                    <li class="breadcrumb-item">
                        <a href="category.php?slug=<?php echo $category['slug']; ?>">
                            <?php echo $category['name']; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </nav>


        <div class="row">
            <!-- Product Images -->
            <div class="col-md-5">
                <div class="product-gallery">
                    <img src="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? $product['image'] : 'uploads/' . $product['image']; ?>"
                        id="main-product-image" class="img-fluid mb-3 rounded" alt="<?php echo $product['name']; ?>">
                    <?php if (count($images) > 1): ?>
                        <div class="row">
                            <?php foreach ($images as $image): ?>
                                <div class="col-3">
                                    <img src="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? $product['image'] : 'uploads/' . $product['image']; ?>"
                                        class="img-fluid product-thumbnail <?php echo $image['is_primary'] ? 'active' : ''; ?>"
                                        onclick="document.getElementById('main-product-image').src='uploads/<?php echo $image['image_path']; ?>'"
                                        alt="<?php echo $product['name']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-md-7">
                <h1 class="mb-3"><?php echo $product['name']; ?></h1>

                <?php if ($product['is_new']): ?>
                    <span class="badge bg-success me-2">Mới</span>
                <?php endif; ?>

                <?php if ($product['featured']): ?>
                    <span class="badge bg-primary">Nổi bật</span>
                <?php endif; ?>

                <p class="text-muted mt-2">Thương hiệu: <strong><?php echo $product['manufacturer']; ?></strong></p>

                <?php
                $current_price = get_current_price($product);
                $has_discount = $current_price < $product['price'];
                ?>
                <div class="mb-3">
                    <?php echo get_rating_stars($product['rating'], $product['rating_count']); ?>
                    <span class="text-muted">(<?php echo $product['rating_count']; ?> đánh giá)</span>
                </div>

                <div class="mb-3">
                    <?php if ($has_discount): ?>
                        <div class="mb-2">
                            <del class="text-muted fs-4"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</del>
                        </div>
                        <div class="fs-3 text-danger fw-bold">
                            <?php echo number_format($current_price, 0, ',', '.'); ?>₫
                        </div>
                        <?php if ($product['discount_end_date']): ?>
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-clock me-2"></i>Thời gian khuyến mãi còn:
                                <span id="countdown" data-end="<?php echo $product['discount_end_date']; ?>"></span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="fs-3 text-danger fw-bold">
                            <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success mb-2">Còn hàng (<?php echo $product['stock']; ?>)</span>

                        <div class="d-flex flex-column flex-md-row align-items-start mt-2">
                            <div class="input-group mb-2 mb-md-0 me-md-3" style="width: 200px;">
                                <button class="btn btn-outline-secondary" type="button" id="decrease-quantity">-</button>
                                <input type="number" class="form-control text-center" id="product-quantity" value="1"
                                    min="1" max="<?php echo $product['stock']; ?>">
                                <button class="btn btn-outline-secondary" type="button" id="increase-quantity">+</button>
                            </div>

                            <button class="btn btn-primary add-to-cart w-100 w-md-auto"
                                data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
                            </button>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-danger mb-3">Hết hàng</span>
                        <p>Vui lòng liên hệ để biết thêm thông tin.</p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <h5>Mô tả sản phẩm</h5>
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
                <?php if (isset($_SESSION['rating_message'])): ?>
                    <div class="alert alert-info">
                        <?php echo $_SESSION['rating_message'];
                        unset($_SESSION['rating_message']); ?>
                    </div>
                <?php endif; ?>
                <?php
                $alreadyRated = false;
                if (isset($_SESSION['user_id'])) {
                    $stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $_SESSION['user_id'], $product['id']);
                    $stmt->execute();
                    $stmt->store_result();
                    $alreadyRated = $stmt->num_rows > 0;
                }
                ?>

                <div class="mb-4">
                    <h5>Đánh giá sản phẩm</h5>

                    <!-- Nếu đã đăng nhập và chưa đánh giá -->
                    <?php if (isset($_SESSION['user_id']) && !$alreadyRated): ?>
                        <form method="POST" class="d-flex align-items-center gap-2">
                            <label for="rating" class="form-label me-2 mb-0">Đánh giá:</label>
                            <select name="rating" id="rating" class="form-select w-auto" required>
                                <option value="">Chọn sao</option>
                                <option value="5">★★★★★ - Tuyệt vời</option>
                                <option value="4">★★★★ - Tốt</option>
                                <option value="3">★★★ - Bình thường</option>
                                <option value="2">★★ - Tệ</option>
                                <option value="1">★ - Rất tệ</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary">Gửi</button>
                        </form>
                    <?php elseif ($alreadyRated): ?>
                        <div class="text-muted mt-2">Bạn đã đánh giá sản phẩm này.</div>
                    <?php else: ?>
                        <div class="text-muted">Vui lòng <a href="login.php">đăng nhập</a> để đánh giá sản phẩm.</div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($specs)): ?>
                    <div class="specs-section mb-4">
                        <h5>Thông số kỹ thuật</h5>
                        <table class="table table-striped">
                            <tbody>
                                <?php foreach ($specs as $spec): ?>
                                    <tr>
                                        <th width="30%"><?php echo $spec['spec_name']; ?></th>
                                        <td><?php echo $spec['spec_value']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h5>Chính sách mua hàng</h5>
                    <ul>
                        <li>Bảo hành chính hãng 12 tháng</li>
                        <li>Đổi trả miễn phí trong 7 ngày</li>
                        <li>Giao hàng toàn quốc</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="mt-5">
                <h3 class="mb-4">Sản phẩm liên quan</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 d-flex flex-column">
                                <img src="<?php echo filter_var($related['image'], FILTER_VALIDATE_URL) ? $related['image'] : 'uploads/' . $related['image']; ?>"
                                    class="card-img-top" alt="<?php echo $related['name']; ?>">
                                <div class="card-body d-flex flex-column flex-grow-1">
                                    <h5 class="card-title"><?php echo $related['name']; ?></h5>
                                    <p class="card-text text-truncate"><?php echo $related['description']; ?></p>
                                    <div class="ratingresult">
                                        <?php echo get_rating_stars($related['rating'], $related['rating_count']); ?>
                                        <span class="text-muted">(<?php echo $related['rating_count']; ?> đánh giá)</span>
                                    </div>
                                    <div class="d-flex flex-column mt-auto">
                                        <div class="d-flex flex-column">
                                            <?php
                                            $current_price = get_current_price($related);
                                            $has_discount = $current_price < $related['price'];
                                            ?>
                                            <?php if ($has_discount): ?>
                                                <p class="mb-0">
                                                    <del
                                                        class="text-muted"><?php echo number_format($related['price'], 0, ',', '.'); ?>₫</del>
                                                </p>
                                                <p class="text-danger fw-bold">
                                                    <?php echo number_format($current_price, 0, ',', '.'); ?>₫
                                                </p>
                                            <?php else: ?>
                                                <p class="text-danger fw-bold">
                                                    <?php echo number_format($related['price'], 0, ',', '.'); ?>₫
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex flex-column align-items-center">
                                            <a href="product.php?slug=<?php echo $related['slug']; ?>"
                                                class="btn btn-primary w-100 mb-2">Chi Tiết</a>
                                            <?php if ($related['stock'] > 0): ?>
                                                <button class="btn btn-success add-to-cart w-100"
                                                    data-product-id="<?php echo $related['id']; ?>">
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
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/search-autocomplete.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Countdown timer for discount
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                const endDate = new Date(countdownElement.dataset.end).getTime();

                const updateCountdown = () => {
                    const now = new Date().getTime();
                    const distance = endDate - now;

                    if (distance < 0) {
                        countdownElement.innerHTML = 'Đã kết thúc';
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    countdownElement.innerHTML = `${days} ngày ${hours} giờ ${minutes} phút ${seconds} giây`;
                };

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }

            // Quantity control
            const quantityInput = document.getElementById('product-quantity');
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');
            const maxStock = <?php echo $product['stock']; ?>;

            decreaseBtn.addEventListener('click', function () {
                let value = parseInt(quantityInput.value, 10);
                value = isNaN(value) ? 1 : value;
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });

            increaseBtn.addEventListener('click', function () {
                let value = parseInt(quantityInput.value, 10);
                value = isNaN(value) ? 1 : value;
                if (value < maxStock) {
                    quantityInput.value = value + 1;
                }
            });
        });
    </script>
</body>

</html>
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="mt-5">
        <h4>Đánh giá sản phẩm</h4>
        <form id="rating-form" method="POST" class="d-flex flex-column" style="max-width: 300px;">
            <label class="mb-2">Chọn số sao:</label>
            <div class="mb-3 d-flex gap-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-star fa-regular star-icon" data-value="<?php echo $i; ?>"></i>
                <?php endfor; ?>
                <input type="hidden" name="rating" id="rating-value">
            </div>
            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
        </form>
    </div>
<?php endif; ?>