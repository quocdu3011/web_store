<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get category slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Kiểm tra slug ở parent_categories trước
$stmt = $conn->prepare("SELECT * FROM parent_categories WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$parent_category = $stmt->get_result()->fetch_assoc();

if ($parent_category) {
    // Nếu là parent, lấy danh sách child categories
    $stmt = $conn->prepare("SELECT * FROM child_categories WHERE parent_id = ?");
    $stmt->bind_param("i", $parent_category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $child_categories = [];
    while ($row = $result->fetch_assoc()) {
        $child_categories[] = $row;
    }
    $original_child_categories = $child_categories;
    $is_parent = true;
} else {
    // Nếu không phải parent, kiểm tra child
    $stmt = $conn->prepare("SELECT c.*, p.name as parent_name, p.slug as parent_slug FROM child_categories c LEFT JOIN parent_categories p ON c.parent_id = p.id WHERE c.slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    $original_category = $category;
    if (!$category) {
        header('Location: index.php');
        exit;
    }
    $is_parent = false;
    // Lấy sản phẩm thuộc child category này
    $products = get_products($category['id']);
}

// Get filters from request
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$manufacturer = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Apply filters
$filters = [];
if ($min_price !== null)
    $filters['min_price'] = $min_price;
if ($max_price !== null)
    $filters['max_price'] = $max_price;
if ($manufacturer)
    $filters['manufacturer'] = $manufacturer;

if (!$is_parent) {
    $products = get_products($category['id'], null, 0, $filters);
    // Apply sorting
    if ($sort === 'price_low') {
        usort($products, function ($a, $b) {
            return $a['price'] - $b['price'];
        });
    } else if ($sort === 'price_high') {
        usort($products, function ($a, $b) {
            return $b['price'] - $a['price'];
        });
    } else if ($sort === 'name') {
        usort($products, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_parent ? $parent_category['name'] : $category['name']; ?> - Danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.6.1/dist/nouislider.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    /*breadcrumb styles*/
    .breadcrumb-wrapper {
        background-color: #fff;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .custom-breadcrumb {
        margin-bottom: 0;
        font-size: 14px;
        color: #222;
        background: none;
    }

    .custom-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
        content: "/";
        padding: 0 8px;
        color: #999;
        font-weight: 400;
    }

    .custom-breadcrumb .breadcrumb-item a {
        text-decoration: none;
        color: #222;
        font-weight: 400;
        transition: color 0.2s ease;
    }

    .custom-breadcrumb .breadcrumb-item a:hover {
        color: #dc3545;
        text-decoration: underline;
    }

    .custom-breadcrumb .breadcrumb-item.active {
        color: #999;
        font-weight: 400;
        pointer-events: none;
    }

    /* === Tùy chỉnh slider noUiSlider === */
    #price-slider {
        margin-top: 10px;
        height: 6px;
        padding: 0;
    }

    .noUi-target {
        background: #ddd;
        border-radius: 4px;
        height: 4px;
        border: none;
    }

    .noUi-connect {
        background: #dc3545;
        /* Màu đỏ giữa 2 nút */
    }

    .noUi-handle {
    all: unset;
    position: absolute;
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    background: #fff !important;
    border: 2px solid #dc3545 !important;
    top: -3px !important;
    cursor: pointer;
    box-shadow: none !important;
}

    .noUi-handle:hover {
        box-shadow: 0 0 5px rgba(220, 53, 69, 0.4);
    }

    .noUi-handle:before,
    .noUi-handle:after {
        display: none;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const breadcrumb = document.querySelector(".custom-breadcrumb");
        if (breadcrumb) {
            breadcrumb.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
</script>

<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="container my-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lọc Sản Phẩm</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$is_parent): ?>
                            <form id="filter-form" method="get" action="category.php">
                                <input type="hidden" name="slug" value="<?php echo $slug; ?>">

                                <!-- Slider Giá -->
                                <div class="filter-section">
                                    <h6>Mức Giá</h6>
                                    <div id="price-slider"></div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span id="slider-min-display"></span>
                                        <span id="slider-max-display"></span>
                                    </div>
                                    <input type="hidden" name="min_price" id="min-price-input">
                                    <input type="hidden" name="max_price" id="max-price-input">
                                </div>
                                <h6 class="mb-2">Khoảng giá</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="priceAll" value="" checked onclick="clearPriceRange()">
                                    <label class="form-check-label" for="priceAll">Tất cả</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price1" onclick="setPriceRange(null, 10000000)">
                                    <label class="form-check-label" for="price1">Dưới 10 triệu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price2" onclick="setPriceRange(10000000, 15000000)">
                                    <label class="form-check-label" for="price2">Từ 10 - 15 triệu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price3" onclick="setPriceRange(15000000, 20000000)">
                                    <label class="form-check-label" for="price3">Từ 15 - 20 triệu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price4" onclick="setPriceRange(20000000, 25000000)">
                                    <label class="form-check-label" for="price4">Từ 20 - 25 triệu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price5" onclick="setPriceRange(25000000, 30000000)">
                                    <label class="form-check-label" for="price5">Từ 25 - 30 triệu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price6" onclick="setPriceRange(30000000, null)">
                                    <label class="form-check-label" for="price6">Trên 30 triệu</label>
                                </div>

                                <!-- Hidden fields để submit về PHP -->
                                <input type="hidden" name="min_price" id="min-price" value="<?php echo $min_price ?? ''; ?>">
                                <input type="hidden" name="max_price" id="max-price" value="<?php echo $max_price ?? ''; ?>">
                                <!-- Manufacturers -->
                                <div class="filter-section mt-3">
                                    <h6>Thương Hiệu</h6>
                                    <input type="text" class="form-control" name="manufacturer"
                                        value="<?php echo htmlspecialchars($manufacturer ?? ''); ?>">
                                </div>

                                <!-- Sort Order -->
                                <div class="filter-section mt-3">
                                    <h6>Sắp Xếp</h6>
                                    <select class="form-select" name="sort">
                                        <option value="newest" <?php if ($sort == 'newest')
                                                                    echo 'selected'; ?>>Mới nhất
                                        </option>
                                        <option value="price_low" <?php if ($sort == 'price_low')
                                                                        echo 'selected'; ?>>Giá tăng
                                            dần</option>
                                        <option value="price_high" <?php if ($sort == 'price_high')
                                                                        echo 'selected'; ?>>Giá
                                            giảm dần</option>
                                        <option value="name" <?php if ($sort == 'name')
                                                                    echo 'selected'; ?>>Tên A-Z</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mt-3">Áp Dụng</button>
                                <a href="category.php?slug=<?php echo $slug; ?>"
                                    class="btn btn-outline-secondary w-100 mt-2">Xóa Bộ Lọc</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="breadcrumb-wrapper mb-3">
                    <ol class="breadcrumb custom-breadcrumb align-items-center">
                        <li class="breadcrumb-item">
                            <a href="index.php" class="d-flex align-items-center text-dark">
                                <i class="bi bi-house-door-fill me-1"></i> Trang chủ
                            </a>
                        </li>

                        <?php if (!$is_parent): ?>
                            <li class="breadcrumb-item">
                                <a href="category.php?slug=<?php echo $original_category['parent_slug']; ?>">
                                    <?php echo $original_category['parent_name']; ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="category.php?slug=<?php echo $original_category['slug']; ?>">
                                    <?php echo $original_category['name']; ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="category.php?slug=<?php echo $parent_category['slug']; ?>">
                                    <?php echo $parent_category['name']; ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($product_name ?? ''); ?>
                        </li>
                    </ol>
                </nav>


                <h1 class="mb-4"><?php echo $is_parent ? $parent_category['name'] : $original_category['name']; ?></h1>
                <?php if ($is_parent): ?>
                    <?php if (empty($original_child_categories)): ?>
                        <div class="alert alert-info">Không có danh mục con nào trong danh mục này.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php
                            // Hỗ trợ cả mảng phẳng và mảng nhiều chiều
                            $first = reset($original_child_categories);
                            if (is_array($first) && isset($first[0]) && is_array($first[0])) {
                                // Mảng nhiều chiều
                                foreach ($original_child_categories as $group) {
                                    foreach ($group as $child) {
                            ?>
                                        <div class="col-md-6 mb-4">z
                                            <div class="card h-100">
                                                <?php if (!empty($child['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($child['image']); ?>" class="card-img-top"
                                                        alt="<?php echo htmlspecialchars($child['name']); ?>">
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <a href="category.php?slug=<?php echo htmlspecialchars($child['slug']); ?>">
                                                            <?php echo htmlspecialchars($child['name']); ?>
                                                        </a>
                                                    </h5>
                                                    <p class="card-text"><?php echo htmlspecialchars($child['description']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                }
                            } else {
                                // Mảng phẳng
                                foreach ($original_child_categories as $child) {
                                    ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <?php if (!empty($child['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($child['image']); ?>" class="card-img-top"
                                                    alt="<?php echo htmlspecialchars($child['name']); ?>">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a href="category.php?slug=<?php echo htmlspecialchars($child['slug']); ?>">
                                                        <?php echo htmlspecialchars($child['name']); ?>
                                                    </a>
                                                </h5>
                                                <p class="card-text"><?php echo htmlspecialchars($child['description']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (!empty($original_category['description'])): ?>
                        <p class="lead mb-4"><?php echo $original_category['description']; ?></p>
                    <?php endif; ?>
                    <?php if (empty($products)): ?>
                        <div class="alert alert-info">Không có sản phẩm nào trong danh mục này.</div>
                    <?php else: ?>
                        <!-- Products count -->
                        <p class="text-muted mb-4">Hiển thị <?php echo count($products); ?> sản phẩm</p>

                        <!-- Products Grid -->
                        <div class="row" id="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 d-flex flex-column">
                                        <img src="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? $product['image'] : 'uploads/' . $product['image']; ?>"
                                            class="card-img-top" alt="<?php echo $product['name']; ?>">
                                        <div class="card-body d-flex flex-column flex-grow-1">
                                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                            <p class="card-text text-truncate"><?php echo $product['description']; ?></p>
                                            <div class="ratingresult">
                                                <?php echo get_rating_stars($product['rating'], $product['rating_count']); ?>
                                                <span class="text-muted">(<?php echo $product['rating_count']; ?> đánh giá)</span>
                                            </div>
                                            <div class="d-flex flex-column mt-auto">
                                                <div class="d-flex flex-column">
                                                    <?php
                                                    $current_price = get_current_price($product);
                                                    $has_discount = $current_price < $product['price'];
                                                    ?>
                                                    <?php if ($has_discount): ?>
                                                        <p class="mb-0">
                                                            <del
                                                                class="text-muted"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</del>
                                                        </p>
                                                        <p class="text-danger fw-bold">
                                                            <?php echo number_format($current_price, 0, ',', '.'); ?>₫</p>
                                                    <?php else: ?>
                                                        <p class="text-danger fw-bold">
                                                            <?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex flex-column align-items-center">
                                                    <a href="product.php?slug=<?php echo $product['slug']; ?>"
                                                        class="btn btn-primary w-100 mb-2">Chi Tiết</a>
                                                    <?php if ($product['stock'] > 0): ?>
                                                        <button class="btn btn-success add-to-cart w-100"
                                                            data-product-id="<?php echo $product['id']; ?>">
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
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.6.1/dist/nouislider.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        const priceSlider = document.getElementById('price-slider');
        const minDisplay = document.getElementById('slider-min-display');
        const maxDisplay = document.getElementById('slider-max-display');
        const minInput = document.getElementById('min-price-input');
        const maxInput = document.getElementById('max-price-input');

        const minStart = <?php echo $min_price ?? 1000000; ?>;
        const maxStart = <?php echo $max_price ?? 50000000; ?>;

        noUiSlider.create(priceSlider, {
            start: [minStart, maxStart],
            connect: true,
            step: 1000000,
            range: {
                min: 0,
                max: 50000000
            },
            format: {
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });

        function formatCurrency(value) {
            return Number(value).toLocaleString('vi-VN') + ' ₫';
        }

        priceSlider.noUiSlider.on('update', function(values) {
            minDisplay.textContent = "Từ: " + formatCurrency(values[0]);
            maxDisplay.textContent = "Đến: " + formatCurrency(values[1]);
            minInput.value = values[0];
            maxInput.value = values[1];
        });
    </script>
    <script>
        function setPriceRange(min, max) {
            const minInput = document.getElementById('min-price');
            const maxInput = document.getElementById('max-price');
            if (minInput) minInput.value = min ?? '';
            if (maxInput) maxInput.value = max ?? '';
        }
    </script>
    <script>
        function setPriceRange(min, max) {
            document.getElementById('min-price').value = min ?? '';
            document.getElementById('max-price').value = max ?? '';
        }

        function clearPriceRange() {
            document.getElementById('min-price').value = '';
            document.getElementById('max-price').value = '';
        }
    </script>


    <script src="assets/js/search-autocomplete.js"></script>
</body>

</html>