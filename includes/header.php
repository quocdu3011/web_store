<?php
require_once __DIR__ . '/../includes/functions.php';
$site_name = get_setting('site_name') ?: 'Chưa có logo';
$logo = get_setting('logo');
?>
<link rel="stylesheet" href="../assets/css/style_header.css">
<header class="header-container text-white">
    <div class="container">
        <div class="row py-3">
            <div class="col-md-4">
                <a href="index.php" class="text-decoration-none">
                    <?php if ($logo): ?>
                        <img class="header-logo" src="<?php echo "uploads/".sanitize_output($logo); ?>" alt="<?php echo sanitize_output($site_name); ?>" class="header-logo">
                    <?php endif; ?>
                </a>
            </div>
            <div class="col-md-4 pt-1">
                <div class="search-autocomplete-wrapper">
                    <form class="d-flex" id="search-form" action="search.php" method="get">
                        <input 
                            class="form-control me-2" 
                            type="search" 
                            name="q" 
                            id="search-input"
                            placeholder="Tìm kiếm sản phẩm..." 
                            value="<?php echo isset($_GET['q']) ? sanitize_output($_GET['q']) : ''; ?>"
                            autocomplete="off"
                        >
                        <button class="btn btn-outline-light" type="submit">Tìm</button>
                    </form>
                    <!-- Autocomplete results will appear here -->
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="btn btn-outline-light me-2">Tài Khoản</a>
                    <a href="logout.php" class="btn btn-light">Đăng Xuất</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Đăng Nhập</a>
                    <a href="register.php" class="btn btn-light">Đăng Ký</a>
                <?php endif; ?>
                <a href="cart.php" class="btn ms-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge bg-danger cart-count"><?php echo get_cart_count(); ?></span>
                </a>
            </div>
        </div>
    </div>
</header> 