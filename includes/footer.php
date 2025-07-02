<?php
require_once __DIR__ . '/../includes/functions.php';
$site_name = get_setting('site_name') ?: 'Cửa Hàng Công Nghệ';
$contact_address = get_setting('contact_address') ?: '123 Đường Công Nghệ, Thành Phố, Quốc Gia';
$contact_phone = get_setting('contact_phone') ?: '+84 234 567 890';
$contact_email = get_setting('contact_email') ?: 'info@techstore.com';
?>
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Về Chúng Tôi</h5>
                <p>Điểm đến tin cậy cho mọi nhu cầu công nghệ. Chúng tôi cung cấp máy ảnh, máy tính và thiết bị mới nhất với giá cạnh tranh.</p>
            </div>
            <div class="col-md-4">
                <h5>Liên Kết Nhanh</h5>
                <ul class="list-unstyled">
                    <li><a href="about.php" class="text-white">Về Chúng Tôi</a></li>
                    <li><a href="contact.php" class="text-white">Liên Hệ</a></li>
                    <li><a href="privacy.php" class="text-white">Chính Sách Bảo Mật</a></li>
                    <li><a href="terms.php" class="text-white">Điều Khoản & Điều Kiện</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Thông Tin Liên Hệ</h5>
                <address>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo sanitize_output($contact_address); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo sanitize_output($contact_phone); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo sanitize_output($contact_email); ?></p>
                </address>
                <div class="social-links">
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo sanitize_output($site_name); ?>. Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </div>
</footer> 