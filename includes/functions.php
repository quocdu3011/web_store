<?php
// Database helper functions
function get_products($category_id = null, $limit = null, $offset = 0, $filters = [], $is_parent = false) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name,
            CASE 
                WHEN p.discount_price IS NOT NULL 
                AND p.discount_start_date IS NOT NULL 
                AND p.discount_end_date IS NOT NULL 
                AND NOW() BETWEEN p.discount_start_date AND p.discount_end_date 
                THEN p.discount_price 
                ELSE p.price 
            END as current_price
            FROM products p 
            LEFT JOIN child_categories c ON p.category_id = c.id 
            WHERE 1=1";
    
    if ($category_id) {
        if ($is_parent) {
            // Lấy tất cả sản phẩm thuộc các child của parent này
            $sql .= " AND c.parent_id = ?";
        } else {
            // Lấy sản phẩm thuộc child cụ thể
            $sql .= " AND p.category_id = ?";
        }
    }
    
    // Apply filters
    if (!empty($filters['min_price'])) {
        $sql .= " AND (
            CASE 
                WHEN p.discount_price IS NOT NULL 
                AND p.discount_start_date IS NOT NULL 
                AND p.discount_end_date IS NOT NULL 
                AND NOW() BETWEEN p.discount_start_date AND p.discount_end_date 
                THEN p.discount_price 
                ELSE p.price 
            END >= " . floatval($filters['min_price']) . ")";
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND (
            CASE 
                WHEN p.discount_price IS NOT NULL 
                AND p.discount_start_date IS NOT NULL 
                AND p.discount_end_date IS NOT NULL 
                AND NOW() BETWEEN p.discount_start_date AND p.discount_end_date 
                THEN p.discount_price 
                ELSE p.price 
            END <= " . floatval($filters['max_price']) . ")";
    }
    if (!empty($filters['manufacturer'])) {
        $sql .= " AND p.manufacturer = '" . $conn->real_escape_string($filters['manufacturer']) . "'";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($category_id) {
        $stmt->bind_param("i", $category_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_product($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_categories($parent_id = null) {
    global $conn;
    
    if ($parent_id === null) {
        // Get parent categories
        $sql = "SELECT * FROM parent_categories ORDER BY name";
        $stmt = $conn->prepare($sql);
    } else {
        // Get child categories for a specific parent
        $sql = "SELECT * FROM child_categories WHERE parent_id = ? ORDER BY name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parent_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_parent_categories() {
    global $conn;
    
    $sql = "SELECT * FROM parent_categories ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_child_categories($parent_id = null) {
    global $conn;
    
    $sql = "SELECT c.*, p.name as parent_name 
            FROM child_categories c 
            LEFT JOIN parent_categories p ON c.parent_id = p.id";
    
    if ($parent_id !== null) {
        $sql .= " WHERE c.parent_id = ?";
    }
    
    $sql .= " ORDER BY c.name";
    
    $stmt = $conn->prepare($sql);
    
    if ($parent_id !== null) {
        $stmt->bind_param("i", $parent_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_category($id, $is_parent = false) {
    global $conn;
    
    $table = $is_parent ? 'parent_categories' : 'child_categories';
    $sql = "SELECT * FROM " . $table . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Authentication functions
function register_user($username, $email, $password, $full_name = '') {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    
    return $stmt->execute();
}

function login_user($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Cart functions
function add_to_cart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function update_cart_quantity($product_id, $quantity) {
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        remove_from_cart($product_id);
    }
}

function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function get_cart_total() {
    global $conn;
    $total = 0;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = get_product($product_id);
            if ($product && $product['price'] > 0) {
                $total += $product['price'] * $quantity;
            }
        }
    }
    
    return $total;
}

function get_cart_count() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

// Format functions
function format_price($price) {
    if ($price == 0) {
        return 'Contact';
    }
    return number_format($price, 2)." đ";
}

function sanitize_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function upload_image($file, $target_dir = "uploads/") {
    // Đường dẫn vật lý tuyệt đối trên server
    $absolute_dir = dirname(__DIR__) . '/' . $target_dir; // Nếu file này nằm trong includes/
    if (!is_dir($absolute_dir)) {
        mkdir($absolute_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $absolute_dir . $filename;
    $relative_file = $target_dir . $filename; // Cái này sẽ lưu vào database

    $check = getimagesize($file["tmp_name"]);
    if($check === false) return false;
    if ($file["size"] > 5000000) return false;
    if(!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) return false;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $relative_file; // Lưu vào database
    }
    return false;
}

// Admin and staff functions
function is_admin() {
    if (!isset($_SESSION['user_id'])) return false;
    
    global $conn;
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        return $user['role'] === 'admin';
    }
    
    return false;
}

function is_staff() {
    if (!isset($_SESSION['user_id'])) return false;
    
    global $conn;
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        return $user['role'] === 'staff' || $user['role'] === 'admin';
    }
    
    return false;
}

// Search function
function search_products($query, $limit = 10) {
    global $conn;
    
    $search = "%" . $conn->real_escape_string($query) . "%";
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN child_categories c ON p.category_id = c.id 
            WHERE p.name LIKE ? 
            OR p.description LIKE ? 
            OR p.manufacturer LIKE ?
            ORDER BY p.created_at DESC
            LIMIT ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $search, $search, $search, $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Filter functions
function get_manufacturers() {
    global $conn;
    
    $sql = "SELECT DISTINCT manufacturer FROM products WHERE manufacturer IS NOT NULL ORDER BY manufacturer";
    $result = $conn->query($sql);
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_price_range() {
    global $conn;
    
    $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE price > 0";
    $result = $conn->query($sql);
    
    return $result->fetch_assoc();
}

function get_setting($key) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

function get_order_status_class($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function get_order_status_text($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ xử lý';
        case 'processing':
            return 'Đang xử lý';
        case 'completed':
            return 'Hoàn thành';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return ucfirst($status);
    }
}

// Get current price of a product (considering discounts)
function get_current_price($product) {
    $now = date('Y-m-d H:i:s');
    if ($product['discount_price'] !== null && 
        $product['discount_start_date'] !== null && 
        $product['discount_end_date'] !== null && 
        $now >= $product['discount_start_date'] && 
        $now <= $product['discount_end_date']) {
        return $product['discount_price'];
    }
    return $product['price'];
}

// Create slug from text
function create_slug($text) {
    // Remove special characters
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    // Replace space with dash
    $text = preg_replace('/\s+/', '-', $text);
    // Convert to lowercase
    $text = strtolower($text);
    // Remove duplicate dashes
    $text = preg_replace('/-+/', '-', $text);
    // Trim dashes from start and end
    $text = trim($text, '-');
    
    return $text;
}

// Order management functions
function get_orders($status = null, $limit = null, $offset = 0) {
    global $conn;
    
    $sql = "SELECT o.*, u.full_name, u.email, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE 1=1";
    
    if ($status) {
        $sql .= " AND o.status = '" . $conn->real_escape_string($status) . "'";
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
    }
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_order($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.full_name, u.email 
                           FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           WHERE o.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_order_items($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image, p.slug
                           FROM order_items oi
                           LEFT JOIN products p ON oi.product_id = p.id
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update_order_status($order_id, $status, $notes = '') {
    global $conn;
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $result = $stmt->execute();
    
    if ($result) {
        // Add to history
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO order_history (order_id, user_id, status, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $order_id, $user_id, $status, $notes);
        $stmt->execute();
    }
    
    return $result;
}

function get_order_history($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT oh.*, u.username, u.full_name, u.role
                           FROM order_history oh
                           LEFT JOIN users u ON oh.user_id = u.id
                           WHERE oh.order_id = ?
                           ORDER BY oh.created_at DESC");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
} 
function get_rating_stars($rating, $rating_count = 1) {
    if ($rating_count <= 0) {
        return '<span class="text-muted">Chưa có đánh giá</span>';
    }
    $numstars = $rating/$rating_count;
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $numstars) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-muted"></i>';
        }
    }
    return $stars;
}