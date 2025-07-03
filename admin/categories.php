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
        $category_id = $_POST['category_id'] ?? null;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $is_parent = (isset($_POST['is_parent']) && $_POST['is_parent'] == '1') ? true : false;
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        
        if (empty($name)) {
            $error = 'Tên danh mục là bắt buộc';
        } elseif (!$is_parent && empty($parent_id)) {
            $error = 'Danh mục con phải có danh mục cha';
        } else {
            $slug = strtolower(str_replace(' ', '-', $name));
            
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
                    if ($is_parent) {
                        $stmt = $conn->prepare("INSERT INTO parent_categories (name, slug, description, image) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $name, $slug, $description, $image);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO child_categories (name, slug, description, parent_id, image) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssis", $name, $slug, $description, $parent_id, $image);
                    }
                } else {
                    if ($is_parent) {
                        $sql = "UPDATE parent_categories SET name = ?, slug = ?, description = ?";
                        if ($image) {
                            $sql .= ", image = ?";
                        }
                        $sql .= " WHERE id = ?";
                        
                        if ($image) {
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssssi", $name, $slug, $description, $image, $category_id);
                        } else {
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssi", $name, $slug, $description, $category_id);
                        }
                    } else {
                        $sql = "UPDATE child_categories SET name = ?, slug = ?, description = ?, parent_id = ?";
                        if ($image) {
                            $sql .= ", image = ?";
                        }
                        $sql .= " WHERE id = ?";
                        
                        if ($image) {
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssisi", $name, $slug, $description, $parent_id, $image, $category_id);
                        } else {
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssii", $name, $slug, $description, $parent_id, $category_id);
                        }
                    }
                }
                
                if ($stmt->execute()) {
                    $message = $action == 'add' ? 'Thêm danh mục thành công' : 'Cập nhật danh mục thành công';
                } else {
                    $error = 'Lỗi: ' . $stmt->error;
                }
            }
        }
    } elseif ($action == 'delete') {
        $category_id = $_POST['category_id'] ?? null;
        $is_parent = isset($_POST['is_parent']) ? true : false;
        
        if ($category_id) {
            if ($is_parent) {
                // Check if parent category has child categories
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM child_categories WHERE parent_id = ?");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $child_count = $result->fetch_assoc()['count'];
                
                if ($child_count > 0) {
                    $error = 'Không thể xóa danh mục cha vì có danh mục con';
                } else {
                    $stmt = $conn->prepare("DELETE FROM parent_categories WHERE id = ?");
                    $stmt->bind_param("i", $category_id);
                }
            } else {
                // Check if child category has products
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product_count = $result->fetch_assoc()['count'];
                
                if ($product_count > 0) {
                    $error = 'Không thể xóa danh mục con vì có sản phẩm liên kết';
                } else {
                    $stmt = $conn->prepare("DELETE FROM child_categories WHERE id = ?");
                    $stmt->bind_param("i", $category_id);
                }
            }
            
            if (!$error && isset($stmt)) {
                if ($stmt->execute()) {
                    $message = 'Xóa danh mục thành công';
                } else {
                    $error = 'Lỗi: ' . $stmt->error;
                }
            }
        }
    }
}

// Get all categories
$parent_categories = get_parent_categories();
$child_categories = get_child_categories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục - Quản Trị</title>
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
                    <h1>Quản Lý Danh Mục</h1>
                    <div>
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#categoryModal" data-category-type="parent">
                            Thêm Danh Mục Cha
                        </button>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-category-type="child">
                            Thêm Danh Mục Con
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo sanitize_output($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo sanitize_output($error); ?></div>
                <?php endif; ?>

                <!-- Parent Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Danh Mục Cha</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình Ảnh</th>
                                        <th>Tên Danh Mục</th>
                                        <th>Slug</th>
                                        <th>Số Danh Mục Con</th>
                                        <th>Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parent_categories as $category): ?>
                                        <?php
                                        $child_count = 0;
                                        foreach ($child_categories as $child) {
                                            if ($child['parent_id'] == $category['id']) {
                                                $child_count++;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <?php if (!empty($category['image'])): ?>
                                                    <img src="../<?php echo sanitize_output($category['image']); ?>" 
                                                         alt="<?php echo sanitize_output($category['name']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo sanitize_output($category['name']); ?></td>
                                            <td><?php echo sanitize_output($category['slug']); ?></td>
                                            <td><?php echo $child_count; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-category" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#categoryModal"
                                                        data-category-id="<?php echo $category['id']; ?>"
                                                        data-category-name="<?php echo sanitize_output($category['name']); ?>"
                                                        data-category-description="<?php echo sanitize_output($category['description']); ?>"
                                                        data-category-type="parent">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-category"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteCategoryModal"
                                                        data-category-id="<?php echo $category['id']; ?>"
                                                        data-category-name="<?php echo sanitize_output($category['name']); ?>"
                                                        data-category-type="parent">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Child Categories -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh Mục Con</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình Ảnh</th>
                                        <th>Tên Danh Mục</th>
                                        <th>Slug</th>
                                        <th>Danh Mục Cha</th>
                                        <th>Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($child_categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <?php if (!empty($category['image'])): ?>
                                                    <img src="../uploads/<?php echo sanitize_output($category['image']); ?>" 
                                                         alt="<?php echo sanitize_output($category['name']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo sanitize_output($category['name']); ?></td>
                                            <td><?php echo sanitize_output($category['slug']); ?></td>
                                            <td><?php echo sanitize_output($category['parent_name']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-category" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#categoryModal"
                                                        data-category-id="<?php echo $category['id']; ?>"
                                                        data-category-name="<?php echo sanitize_output($category['name']); ?>"
                                                        data-category-description="<?php echo sanitize_output($category['description']); ?>"
                                                        data-category-parent-id="<?php echo $category['parent_id']; ?>"
                                                        data-category-type="child">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-category"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteCategoryModal"
                                                        data-category-id="<?php echo $category['id']; ?>"
                                                        data-category-name="<?php echo sanitize_output($category['name']); ?>"
                                                        data-category-type="child">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm/Sửa Danh Mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add" id="category-action">
                        <input type="hidden" name="category_id" id="category-id">
                        <input type="hidden" name="is_parent" id="category-is-parent" value="1">

                        <div class="mb-3">
                            <label for="category-name" class="form-label">Tên Danh Mục*</label>
                            <input type="text" class="form-control" id="category-name" name="name" required>
                        </div>

                        <div class="mb-3" id="parent-category-group">
                            <label for="category-parent" class="form-label">Danh Mục Cha</label>
                            <select class="form-select" id="category-parent" name="parent_id">
                                <option value="">Chọn Danh Mục Cha</option>
                                <?php foreach ($parent_categories as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>">
                                        <?php echo sanitize_output($parent['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category-description" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="category-description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="category-image" class="form-label">Hình Ảnh</label>
                            <input type="file" class="form-control" id="category-image" name="image" accept="image/*">
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu Danh Mục</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác Nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa danh mục này?</p>
                    <p class="fw-bold" id="delete-category-name"></p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" id="delete-category-id">
                        <input type="hidden" name="is_parent" id="delete-is-parent" value="0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryForm = document.getElementById('categoryForm');
            const categoryAction = document.getElementById('category-action');
            const categoryId = document.getElementById('category-id');
            const categoryName = document.getElementById('category-name');
            const categoryDescription = document.getElementById('category-description');
            const categoryParent = document.getElementById('category-parent');
            const categoryIsParent = document.getElementById('category-is-parent');
            const parentCategoryGroup = document.getElementById('parent-category-group');
            
            // Handle category type selection
            document.querySelectorAll('[data-category-type]').forEach(button => {
                button.addEventListener('click', function() {
                    const isParent = this.dataset.categoryType === 'parent';
                    categoryIsParent.value = isParent ? '1' : '0';
                    parentCategoryGroup.style.display = isParent ? 'none' : 'block';
                    categoryForm.reset();
                    categoryAction.value = 'add';
                    categoryId.value = '';
                });
            });
            
            // Handle edit category
            document.querySelectorAll('.edit-category').forEach(button => {
                button.addEventListener('click', function() {
                    const isParent = this.dataset.categoryType === 'parent';
                    categoryAction.value = 'edit';
                    categoryId.value = this.dataset.categoryId;
                    categoryName.value = this.dataset.categoryName;
                    categoryDescription.value = this.dataset.categoryDescription;
                    categoryIsParent.value = isParent ? '1' : '0';
                    parentCategoryGroup.style.display = isParent ? 'none' : 'block';
                    
                    if (!isParent) {
                        categoryParent.value = this.dataset.categoryParentId;
                    }
                });
            });
            
            // Handle delete category
            document.querySelectorAll('.delete-category').forEach(button => {
                button.addEventListener('click', function() {
                    const isParent = this.dataset.categoryType === 'parent';
                    document.getElementById('delete-category-id').value = this.dataset.categoryId;
                    document.getElementById('delete-category-name').textContent = this.dataset.categoryName;
                    document.getElementById('delete-is-parent').value = isParent ? '1' : '0';
                });
            });
        });
    </script>
</body>
</html> 