<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Define settings
$settings = [
    'site_name' => 'Tên Cửa Hàng',
    'site_description' => 'Mô Tả Cửa Hàng',
    'contact_email' => 'Email Liên Hệ',
    'contact_phone' => 'Số Điện Thoại Liên Hệ',
    'contact_address' => 'Địa Chỉ Liên Hệ',
    'shipping_fee' => 'Phí Vận Chuyển (₫)',
    'enable_social_login' => 'Cho Phép Đăng Nhập Qua Mạng Xã Hội'
];

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update settings
    foreach ($settings as $key => $label) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            
            // Check if setting exists
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM settings WHERE setting_key = ?");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->bind_param("ss", $value, $key);
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->bind_param("ss", $key, $value);
            }
            
            if (!$stmt->execute()) {
                $error = 'Lỗi khi cập nhật cài đặt: ' . $stmt->error;
                break;
            }
        }
    }
    
    if (!$error) {
        $message = 'Cài đặt đã được cập nhật thành công';
    }
}

// Get current settings
$current_settings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt Hệ Thống - Quản Trị</title>
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
                    <h1>Cài Đặt Hệ Thống</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-4">Thông Tin Cửa Hàng</h5>
                                    
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Tên Cửa Hàng</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo sanitize_output($current_settings['site_name'] ?? 'Cửa Hàng Công Nghệ'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">Mô Tả Cửa Hàng</label>
                                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo sanitize_output($current_settings['site_description'] ?? 'Cửa hàng chuyên cung cấp sản phẩm công nghệ chính hãng'); ?></textarea>
                                    </div>
                                    
                                    <h5 class="mb-3 mt-4">Thông Tin Liên Hệ</h5>
                                    
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Email Liên Hệ</label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                               value="<?php echo sanitize_output($current_settings['contact_email'] ?? 'contact@techstore.com'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contact_phone" class="form-label">Số Điện Thoại</label>
                                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                               value="<?php echo sanitize_output($current_settings['contact_phone'] ?? '0123 456 789'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contact_address" class="form-label">Địa Chỉ</label>
                                        <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo sanitize_output($current_settings['contact_address'] ?? '123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh'); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="mb-4">Cài Đặt Bán Hàng</h5>
                                    
                                    <div class="mb-3">
                                        <label for="shipping_fee" class="form-label">Phí Vận Chuyển (₫)</label>
                                        <input type="number" class="form-control" id="shipping_fee" name="shipping_fee" 
                                               value="<?php echo sanitize_output($current_settings['shipping_fee'] ?? '30000'); ?>" min="0">
                                    </div>
                                    
                                    <h5 class="mb-3 mt-4">Cài Đặt Đăng Nhập</h5>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable_social_login" name="enable_social_login" value="1"
                                               <?php if (isset($current_settings['enable_social_login']) && $current_settings['enable_social_login'] == '1') echo 'checked'; ?>>
                                        <label class="form-check-label" for="enable_social_login">Cho phép đăng nhập qua mạng xã hội</label>
                                    </div>
                                    
                                    <h5 class="mb-3 mt-4">Bảo Trì Hệ Thống</h5>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="clear_cache.php" class="btn btn-warning">Xóa Cache</a>
                                        <a href="phpinfo.php" class="btn btn-info" target="_blank">Xem Thông Tin PHP</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary">Lưu Cài Đặt</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 