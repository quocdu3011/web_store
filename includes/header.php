<?php
require_once __DIR__ . '/../includes/functions.php';
include 'includes/cart_icon.php'; // Include the cart icon
$site_name = get_setting('site_name') ?: 'Chưa có tên website';
$logo = get_setting('logo');
?>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .header-container {
        background-color: #dc3545;
        transition: transform 0.4s ease, opacity 0.4s ease, box-shadow 0.3s ease;
    }

    .header-container.fixed {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .header-container.hidden {
        transform: translateY(-100%);
        opacity: 0;
    }

    /* Hiện lại header */
    .header-container.visible {
        transform: translateY(0);
        opacity: 1;
    }

    .header-logo {
        height: 48px;
    }

    .avatar-btn {
        border: none;
        background: transparent;
        padding: 0;
        margin-left: 10px;
        position: relative;
        outline: none !important;
    }

    .avatar-btn:focus {
        box-shadow: none;
    }

    .avatar-img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        transition: transform 0.2s ease;
    }

    .avatar-img:hover {
        transform: scale(1.05);
        cursor: pointer;
    }

    .user-dropdown {
        position: absolute;
        top: 55px;
        right: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        display: none;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .user-dropdown.show {
        display: block;
        opacity: 1;
    }

    .user-dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
    }

    .user-dropdown a:hover {
        background-color: #f1f1f1;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .btn-login {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 6px 18px;
        border-radius: 20px;
        transition: background-color 0.2s ease;
    }

    .btn-login:hover {
        background-color: #bb2d3b;
        color: white;
    }

    .btn-register {
        background-color: white;
        color: #dc3545;
        border: 2px solid #dc3545;
        padding: 6px 18px;
        border-radius: 20px;
        transition: all 0.2s ease;
    }

    .btn-register:hover {
        background-color: #dc3545;
        color: white;
    }
</style>

<header class="header-container text-white">
    <div class="container">
        <div class="row py-3 align-items-center">
            <!-- LOGO -->
            <div class="col-md-4">
                <a href="index.php" class="text-decoration-none">
                    <?php if ($logo): ?>
                        <img class="header-logo" src="<?php echo "uploads/" . sanitize_output($logo); ?>"
                            alt="<?php echo sanitize_output($site_name); ?>">
                    <?php else: ?>
                        <span class="text-white fs-4"><?php echo $site_name; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- SEARCH -->
            <div class="col-md-4 pt-1">
                <form class="d-flex" action="search.php" method="get">
                    <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm sản phẩm..."
                        value="<?php echo isset($_GET['q']) ? sanitize_output($_GET['q']) : ''; ?>" autocomplete="off">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>

                </form>
            </div>

            <!-- TÀI KHOẢN / GIỎ HÀNG -->
            <div class="col-md-4 text-end">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    // Lấy thông tin người dùng
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT avatar, role FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($avatar, $role);
                    $stmt->fetch();
                    $stmt->close();

                    $avatar = $avatar ?: "assets/img/default-avatar.png";
                    $role = $role ?? 'customer';
                    ?>
                    <div class="avatar-wrapper" id="avatar-wrapper">
                        <button class="avatar-btn" id="avatarBtn">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar" class="avatar-img">
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="account.php"><i class="bi bi-person-circle"></i> Thông tin tài khoản</a>
                            <a href="notifications.php"><i class="bi bi-bell-fill"></i> Thông báo</a>
                            <?php if ($role === 'admin'): ?>
                                <a href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Bảng quản trị</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-login me-2">Đăng Nhập</a>
                    <a href="register.php" class="btn btn-register">Đăng Ký</a>

                <?php endif; ?>

                <!-- GIỎ HÀNG -->
                <a href="cart.php" class="btn ms-2 text-white position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span
                        class="badge bg-warning text-dark cart-count position-absolute top-0 start-100 translate-middle p-1 rounded-circle">
                        <?php echo get_cart_count(); ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const avatarBtn = document.getElementById('avatarBtn');
        const dropdown = document.getElementById('userDropdown');

        // Toggle dropdown
        avatarBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        // Click ngoài -> đóng
        document.addEventListener('click', function () {
            dropdown.classList.remove('show');
        });

        // Cuộn trang -> đóng
        window.addEventListener('scroll', function () {
            dropdown.classList.remove('show');
        });

        // Resize trình duyệt -> đóng
        window.addEventListener('resize', function () {
            dropdown.classList.remove('show');
        });
    });
    let lastScroll = 0;
    const header = document.getElementsByClassName('header-container')[0];

    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;

      if (currentScroll <= 50) {
        // Gần đầu trang → trở lại trạng thái bình thường
        header.classList.remove('fixed', 'hidden', 'visible');
      } else if (currentScroll > lastScroll && currentScroll > 100) {
        // Cuộn xuống > 100px → ẩn header
        header.classList.add('fixed', 'hidden');
        header.classList.remove('visible');
      } else if (currentScroll < lastScroll) {
        // Cuộn lên → hiện lại header dạng fixed
        header.classList.add('fixed', 'visible');
        header.classList.remove('hidden');
      }

      lastScroll = currentScroll;
    });
</script>