<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Ensure only admin or staff can access this file
session_start();
if (!isset($_SESSION['user_id']) || !(is_admin() || is_staff())) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$products = [];

// Get all products for selection
$sql = "SELECT p.id, p.name, p.image, c.name as category 
        FROM products p 
        JOIN child_categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
try {
    $products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Không thể lấy danh sách sản phẩm: " . $e->getMessage();
}

// Handle image download and upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process single image
    if (isset($_POST['download_image']) && isset($_POST['product_id']) && isset($_POST['image_url'])) {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $image_url = filter_input(INPUT_POST, 'image_url', FILTER_SANITIZE_URL);
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

        if (!$product_id || !$image_url) {
            $error = "ID sản phẩm không hợp lệ hoặc URL ảnh không hợp lệ";
        } else {
            // Get product info to create folder
            $stmt = $db->prepare("SELECT name, slug FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $error = "Không tìm thấy sản phẩm";
            } else {
                $filename = basename($image_url);
                $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
                
                // If no extension, assume jpg
                if (empty($file_ext)) {
                    $file_ext = 'jpg';
                    $filename .= '.jpg';
                }
                
                // Sanitize filename
                $clean_name = $product['slug'] . '-' . uniqid() . '.' . $file_ext;
                
                // Create product folder if it doesn't exist
                $upload_dir = 'uploads/products/' . $product['slug'];
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $save_path = $upload_dir . '/' . $clean_name;
                
                // Download and save the image
                $downloaded = false;
                
                // Try with file_get_contents first
                if (ini_get('allow_url_fopen')) {
                    try {
                        $image_data = @file_get_contents($image_url);
                        if ($image_data !== false) {
                            file_put_contents($save_path, $image_data);
                            $downloaded = true;
                        }
                    } catch (Exception $e) {
                        // Fall back to cURL
                    }
                }
                
                // Try with cURL if file_get_contents failed
                if (!$downloaded && function_exists('curl_init')) {
                    $ch = curl_init($image_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
                    $image_data = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($http_code === 200) {
                        file_put_contents($save_path, $image_data);
                        $downloaded = true;
                    }
                }
                
                if ($downloaded) {
                    // Update database
                    try {
                        // If this is primary, set all other images to non-primary
                        if ($is_primary) {
                            $stmt = $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                            $stmt->execute([$product_id]);
                            
                            // Also update main product image
                            $stmt = $db->prepare("UPDATE products SET image = ? WHERE id = ?");
                            $stmt->execute([$save_path, $product_id]);
                        }
                        
                        // Insert into product_images table
                        $stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$product_id, $save_path, $is_primary]);
                        
                        $success = "Tải ảnh thành công và đã lưu vào cơ sở dữ liệu";
                        
                        // Refresh product list
                        $products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $error = "Lỗi khi cập nhật cơ sở dữ liệu: " . $e->getMessage();
                    }
                } else {
                    $error = "Không thể tải ảnh từ URL đã cung cấp. Vui lòng kiểm tra lại URL.";
                }
            }
        }
    }
    
    // Process bulk images
    if (isset($_POST['bulk_download']) && isset($_POST['product_id']) && isset($_POST['bulk_urls'])) {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $bulk_urls = trim($_POST['bulk_urls']);
        
        if (!$product_id || empty($bulk_urls)) {
            $error = "ID sản phẩm không hợp lệ hoặc không có URL ảnh nào";
        } else {
            $urls = preg_split('/\r\n|\r|\n/', $bulk_urls);
            $urls = array_filter($urls); // Remove empty lines
            
            // Get product info to create folder
            $stmt = $db->prepare("SELECT name, slug FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $error = "Không tìm thấy sản phẩm";
            } else {
                // Create product folder if it doesn't exist
                $upload_dir = 'uploads/products/' . $product['slug'];
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $success_count = 0;
                $error_count = 0;
                
                foreach ($urls as $index => $image_url) {
                    $image_url = filter_var(trim($image_url), FILTER_SANITIZE_URL);
                    if (empty($image_url)) continue;
                    
                    $filename = basename($image_url);
                    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    // If no extension, assume jpg
                    if (empty($file_ext)) {
                        $file_ext = 'jpg';
                        $filename .= '.jpg';
                    }
                    
                    // Sanitize filename
                    $clean_name = $product['slug'] . '-' . uniqid() . '.' . $file_ext;
                    $save_path = $upload_dir . '/' . $clean_name;
                    
                    // Download and save the image using file_get_contents or cURL
                    $downloaded = false;
                    
                    // Try with file_get_contents first
                    if (ini_get('allow_url_fopen')) {
                        try {
                            $image_data = @file_get_contents($image_url);
                            if ($image_data !== false) {
                                file_put_contents($save_path, $image_data);
                                $downloaded = true;
                            }
                        } catch (Exception $e) {
                            // Fall back to cURL
                        }
                    }
                    
                    // Try with cURL if file_get_contents failed
                    if (!$downloaded && function_exists('curl_init')) {
                        $ch = curl_init($image_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
                        $image_data = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        if ($http_code === 200) {
                            file_put_contents($save_path, $image_data);
                            $downloaded = true;
                        }
                    }
                    
                    if ($downloaded) {
                        // Insert into product_images table
                        try {
                            // Make the first image primary if no images exist yet
                            $is_primary = 0;
                            if ($index === 0) {
                                $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
                                $stmt->execute([$product_id]);
                                $image_count = $stmt->fetchColumn();
                                
                                if ($image_count == 0) {
                                    $is_primary = 1;
                                    // Update main product image
                                    $stmt = $db->prepare("UPDATE products SET image = ? WHERE id = ?");
                                    $stmt->execute([$save_path, $product_id]);
                                }
                            }
                            
                            $stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                            $stmt->execute([$product_id, $save_path, $is_primary]);
                            $success_count++;
                        } catch (PDOException $e) {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }
                
                if ($success_count > 0) {
                    $success = "Đã tải thành công $success_count ảnh";
                    if ($error_count > 0) {
                        $success .= " và không thể tải $error_count ảnh";
                    }
                    
                    // Refresh product list
                    $products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $error = "Không thể tải bất kỳ ảnh nào. Vui lòng kiểm tra các URL đã cung cấp.";
                }
            }
        }
    }
}

// Get current product images
$product_images = [];
if (isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($product_id) {
        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$product_id]);
        $product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get product details
        $stmt = $db->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tải ảnh sản phẩm</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .image-item {
            position: relative;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
            width: 150px;
        }
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-item .badge {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .nav-tabs .nav-link {
            color: #333;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Tải ảnh sản phẩm từ web</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="imageTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="single-tab" data-toggle="tab" href="#single" role="tab">Tải một ảnh</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="bulk-tab" data-toggle="tab" href="#bulk" role="tab">Tải nhiều ảnh</a>
                            </li>
                            <?php if (!empty($product_images)): ?>
                            <li class="nav-item">
                                <a class="nav-link" id="manage-tab" data-toggle="tab" href="#manage" role="tab">
                                    Quản lý ảnh (<?php echo count($product_images); ?>)
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="imageTabsContent">
                            <!-- Single Image Tab -->
                            <div class="tab-pane fade show active" id="single" role="tabpanel">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="product_id">Chọn sản phẩm:</label>
                                        <select name="product_id" id="product_id" class="form-control" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>" <?php echo (isset($_GET['id']) && $_GET['id'] == $product['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $product['name'] . ' (' . $product['category'] . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="image_url">URL ảnh:</label>
                                        <input type="url" name="image_url" id="image_url" class="form-control" required placeholder="https://example.com/image.jpg">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_primary" id="is_primary" class="form-check-input">
                                        <label for="is_primary" class="form-check-label">Đặt làm ảnh chính</label>
                                    </div>
                                    <button type="submit" name="download_image" class="btn btn-primary">Tải ảnh</button>
                                </form>
                            </div>
                            
                            <!-- Bulk Images Tab -->
                            <div class="tab-pane fade" id="bulk" role="tabpanel">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="bulk_product_id">Chọn sản phẩm:</label>
                                        <select name="product_id" id="bulk_product_id" class="form-control" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>" <?php echo (isset($_GET['id']) && $_GET['id'] == $product['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $product['name'] . ' (' . $product['category'] . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="bulk_urls">URL ảnh (mỗi URL trên một dòng):</label>
                                        <textarea name="bulk_urls" id="bulk_urls" class="form-control" rows="6" required placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"></textarea>
                                    </div>
                                    <p class="text-muted small">Ảnh đầu tiên sẽ được đặt làm ảnh chính nếu sản phẩm chưa có ảnh.</p>
                                    <button type="submit" name="bulk_download" class="btn btn-primary">Tải các ảnh</button>
                                </form>
                            </div>
                            
                            <!-- Manage Images Tab -->
                            <?php if (!empty($product_images)): ?>
                            <div class="tab-pane fade" id="manage" role="tabpanel">
                                <h5>Ảnh của sản phẩm: <?php echo $selected_product['name']; ?></h5>
                                <div class="image-preview">
                                    <?php foreach ($product_images as $image): ?>
                                        <div class="image-item">
                                            <img src="<?php echo $image['image_path']; ?>" alt="Product image">
                                            <?php if ($image['is_primary']): ?>
                                                <span class="badge badge-primary">Ảnh chính</span>
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <form method="post" action="admin/manage_product_images.php" class="d-inline">
                                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                    <input type="hidden" name="product_id" value="<?php echo $image['product_id']; ?>">
                                                    <?php if (!$image['is_primary']): ?>
                                                        <button type="submit" name="make_primary" class="btn btn-sm btn-outline-primary" title="Đặt làm ảnh chính">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="submit" name="delete_image" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa ảnh này?')" title="Xóa ảnh">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hướng dẫn sử dụng</h5>
                    </div>
                    <div class="card-body">
                        <h6>Tải một ảnh</h6>
                        <ol>
                            <li>Chọn sản phẩm cần thêm ảnh</li>
                            <li>Nhập URL ảnh từ trang web (tìm ảnh trên Google, nhấn chuột phải, chọn "Sao chép địa chỉ hình ảnh")</li>
                            <li>Tích vào "Đặt làm ảnh chính" nếu muốn ảnh này là ảnh đại diện chính</li>
                            <li>Nhấn "Tải ảnh"</li>
                        </ol>
                        
                        <h6>Tải nhiều ảnh</h6>
                        <ol>
                            <li>Chọn sản phẩm cần thêm ảnh</li>
                            <li>Nhập nhiều URL ảnh, mỗi URL trên một dòng</li>
                            <li>Nhấn "Tải các ảnh"</li>
                        </ol>
                        
                        <div class="alert alert-info">
                            <small>Lưu ý: Ảnh sẽ được tải về và lưu trong thư mục uploads/products/[slug-sản-phẩm]/</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple preview for single image
        document.getElementById('image_url').addEventListener('input', function() {
            const previewContainer = document.getElementById('image_preview');
            if (!previewContainer) {
                const container = document.createElement('div');
                container.id = 'image_preview';
                container.style.marginTop = '15px';
                container.style.maxWidth = '200px';
                
                this.parentNode.appendChild(container);
                
                const img = document.createElement('img');
                img.style.maxWidth = '100%';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '4px';
                img.style.padding = '5px';
                container.appendChild(img);
            }
            
            const img = document.querySelector('#image_preview img');
            img.src = this.value;
        });
        
        // Handle tab switching
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab === 'bulk') {
            document.getElementById('bulk-tab').click();
        } else if (tab === 'manage') {
            const manageTab = document.getElementById('manage-tab');
            if (manageTab) manageTab.click();
        }
        
        // Sync product selection between tabs
        document.getElementById('product_id').addEventListener('change', function() {
            document.getElementById('bulk_product_id').value = this.value;
        });
        
        document.getElementById('bulk_product_id').addEventListener('change', function() {
            document.getElementById('product_id').value = this.value;
        });
    });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 