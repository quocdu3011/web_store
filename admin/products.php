<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin() && !is_staff()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'edit') {
        $product_id = $_POST['product_id'] ?? null;
        $name = $_POST['name'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : null;
        $discount_start_date = !empty($_POST['discount_start_date']) ? $_POST['discount_start_date'] : null;
        $discount_end_date = !empty($_POST['discount_end_date']) ? $_POST['discount_end_date'] : null;
        $stock = $_POST['stock'] ?? 0;
        $manufacturer = $_POST['manufacturer'] ?? '';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        
        if (empty($name) || empty($category_id)) {
            $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
        } else {
            $slug = create_slug($name);
            
            // Validate discount
            if ($discount_price !== null) {
                if ($discount_price >= $price) {
                    $error = 'Giá khuyến mãi phải thấp hơn giá gốc';
                } elseif (empty($discount_start_date) || empty($discount_end_date)) {
                    $error = 'Vui lòng chọn thời gian khuyến mãi';
                } elseif (strtotime($discount_end_date) <= strtotime($discount_start_date)) {
                    $error = 'Thời gian kết thúc phải sau thời gian bắt đầu khuyến mãi';
                }
            }
            
            if (!$error) {
                // Handle image upload
                $image = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image = upload_image($_FILES['image']);
                    if (!$image) {
                        $error = 'Không thể tải lên hình ảnh';
                    }
                }
                
                if (!$error) {
                    if ($action == 'add') {
                        $stmt = $conn->prepare("INSERT INTO products (name, slug, category_id, description, price, discount_price, discount_start_date, discount_end_date, stock, manufacturer, image, featured, is_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssisssssissii", $name, $slug, $category_id, $description, $price, $discount_price, $discount_start_date, $discount_end_date, $stock, $manufacturer, $image, $featured, $is_new);
                    } else {
                        $sql = "UPDATE products SET name = ?, slug = ?, category_id = ?, description = ?, price = ?, discount_price = ?, discount_start_date = ?, discount_end_date = ?, stock = ?, manufacturer = ?, featured = ?, is_new = ?";
                        if ($image) {
                            $sql .= ", image = ?";
                        }
                        $sql .= " WHERE id = ?";
                        
                        $stmt = $conn->prepare($sql);
                        if ($image) {
                            $stmt->bind_param("ssisssssissiii", $name, $slug, $category_id, $description, $price, $discount_price, $discount_start_date, $discount_end_date, $stock, $manufacturer, $featured, $is_new, $image, $product_id);
                        } else {
                            $stmt->bind_param("ssisssssissii", $name, $slug, $category_id, $description, $price, $discount_price, $discount_start_date, $discount_end_date, $stock, $manufacturer, $featured, $is_new, $product_id);
                        }
                    }
                    
                    if ($stmt->execute()) {
                        $message = $action == 'add' ? 'Thêm sản phẩm thành công' : 'Cập nhật sản phẩm thành công';
                        // Lưu thông số sản phẩm
                        $new_product_id = $action == 'add' ? $conn->insert_id : $product_id;
                        if (isset($_POST['spec_name'], $_POST['spec_value'])) {
                            $spec_names = $_POST['spec_name'];
                            $spec_values = $_POST['spec_value'];
                            // Nếu là sửa thì xóa thông số cũ
                            if ($action == 'edit') {
                                $conn->query("DELETE FROM product_specs WHERE product_id = " . (int)$new_product_id);
                            }
                            $insert_spec = $conn->prepare("INSERT INTO product_specs (product_id, spec_name, spec_value) VALUES (?, ?, ?)");
                            for ($i = 0; $i < count($spec_names); $i++) {
                                $name = trim($spec_names[$i]);
                                $value = trim($spec_values[$i]);
                                if ($name !== '' && $value !== '') {
                                    $insert_spec->bind_param("iss", $new_product_id, $name, $value);
                                    $insert_spec->execute();
                                }
                            }
                        }
                    } else {
                        $error = 'Lỗi: ' . $stmt->error;
                    }
                }
            }
        }
    } elseif ($action == 'delete') {
        $product_id = $_POST['product_id'] ?? null;
        
        if ($product_id) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            
            if ($stmt->execute()) {
                $message = 'Xóa sản phẩm thành công';
            } else {
                $error = 'Lỗi: ' . $stmt->error;
            }
        }
    }
}

// Get all products
$products = get_products();

// Get all categories for the form
$categories = get_child_categories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Quản Trị</title>
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
                    <h1>Quản Lý Sản Phẩm</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                        Thêm Sản Phẩm Mới
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Danh Mục</th>
                                <th>Thương Hiệu</th>
                                <th>Giá</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo $product['image'] ? '../uploads/' . sanitize_output($product['image']) : '../assets/images/no-image.jpg'; ?>"
                                             alt="<?php echo sanitize_output($product['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo sanitize_output($product['name']); ?></td>
                                    <td><?php echo sanitize_output($product['category_name']); ?></td>
                                    <td><?php echo sanitize_output($product['manufacturer']); ?></td>
                                    <td><?php echo format_price($product['price']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-product" 
                                                data-product='<?php echo json_encode($product); ?>'
                                                data-bs-toggle="modal" data-bs-target="#productModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-product"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo sanitize_output($product['name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm/Sửa Sản Phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="">

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Sản Phẩm*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Danh Mục*</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Chọn Danh Mục Con</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo sanitize_output($category['parent_name'] . ' - ' . $category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Giá Gốc*</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="price" name="price" required min="0" step="1000">
                                <span class="input-group-text">₫</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="discount_price" class="form-label">Giá Khuyến Mãi</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="discount_price" name="discount_price" min="0" step="1000">
                                <span class="input-group-text">₫</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_start_date" class="form-label">Ngày Bắt Đầu Khuyến Mãi</label>
                                    <input type="datetime-local" class="form-control" id="discount_start_date" name="discount_start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_end_date" class="form-label">Ngày Kết Thúc Khuyến Mãi</label>
                                    <input type="datetime-local" class="form-control" id="discount_end_date" name="discount_end_date">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="stock" class="form-label">Số Lượng Tồn Kho*</label>
                            <input type="number" class="form-control" id="stock" name="stock" required min="0">
                        </div>

                        <div class="mb-3">
                            <label for="manufacturer" class="form-label">Thương Hiệu</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Hình Ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div id="current-image" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thông Số Sản Phẩm</label>
                            <div id="specs-container">
                                <div class="row mb-2 spec-row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="spec_name[]" placeholder="Tên thông số">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="spec_value[]" placeholder="Giá trị">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-spec">Xóa</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-spec">Thêm thông số</button>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">Sản phẩm nổi bật</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_new" name="is_new">
                                    <label class="form-check-label" for="is_new">Sản phẩm mới</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu Sản Phẩm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác Nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa sản phẩm này?</p>
                    <p class="text-danger" id="deleteProductName"></p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit product button
        document.querySelectorAll('.edit-product').forEach(button => {
            button.addEventListener('click', function() {
                const product = JSON.parse(this.dataset.product);
                const form = document.getElementById('productForm');
                
                form.elements['action'].value = 'edit';
                form.elements['product_id'].value = product.id;
                form.elements['name'].value = product.name;
                form.elements['category_id'].value = product.category_id;
                form.elements['manufacturer'].value = product.manufacturer;
                form.elements['price'].value = product.price;
                form.elements['description'].value = product.description;

                // Load product specs
                fetch('get_product_specs.php?product_id=' + product.id)
                    .then(res => res.json())
                    .then(specs => {
                        const container = document.getElementById('specs-container');
                        container.innerHTML = '';
                        if (specs.length === 0) {
                            // Nếu không có thông số, thêm 1 dòng trống
                            const row = document.createElement('div');
                            row.className = 'row mb-2 spec-row';
                            row.innerHTML = `
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="spec_name[]" placeholder="Tên thông số">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="spec_value[]" placeholder="Giá trị">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-spec">Xóa</button>
                                </div>
                            `;
                            container.appendChild(row);
                        } else {
                            specs.forEach(spec => {
                                const row = document.createElement('div');
                                row.className = 'row mb-2 spec-row';
                                row.innerHTML = `
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="spec_name[]" value="${spec.spec_name}" placeholder="Tên thông số">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="spec_value[]" value="${spec.spec_value}" placeholder="Giá trị">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-spec">Xóa</button>
                                    </div>
                                `;
                                container.appendChild(row);
                            });
                        }
                    });
            });
        });

        // Handle delete product button
        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                
                document.getElementById('deleteProductId').value = productId;
                document.getElementById('deleteProductName').textContent = productName;
                
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
        });

        // Reset form when adding new product
        document.querySelector('[data-bs-target="#productModal"]').addEventListener('click', function() {
            const form = document.getElementById('productForm');
            form.reset();
            form.elements['action'].value = 'add';
            form.elements['product_id'].value = '';
        });

        // Thêm/xóa dòng thông số sản phẩm
        document.getElementById('add-spec').addEventListener('click', function() {
            const container = document.getElementById('specs-container');
            const row = document.createElement('div');
            row.className = 'row mb-2 spec-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="spec_name[]" placeholder="Tên thông số">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="spec_value[]" placeholder="Giá trị">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-spec">Xóa</button>
                </div>
            `;
            container.appendChild(row);
        });
        document.getElementById('specs-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec')) {
                e.target.closest('.spec-row').remove();
            }
        });
    </script>
</body>
</html>