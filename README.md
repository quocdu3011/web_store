# Website Bán Hàng (PHP & MySQL)

Website bán hàng cho các sản phẩm: điện thoại, laptop, phụ kiện, thiết bị thông minh...  
Giao diện đẹp, quản trị dễ dàng, hỗ trợ đăng nhập Google/Facebook, lọc sản phẩm, giỏ hàng, đặt hàng, quản lý đơn hàng.

## 1. Yêu cầu hệ thống
- Composer (để cài đặt thư viện Google/Facebook SDK)
- XAMPP/WAMP hoặc hosting hỗ trợ PHP/MySQL

## 2. Cài đặt

### Bước 1: Clone hoặc giải nén mã nguồn

Giải nén hoặc clone mã nguồn vào thư mục gốc web (ví dụ: `htdocs/tech_store`).

### Bước 2: Cài đặt kích hoạt apache và mysql

Chạy file cmd: apache_start.bat và mysql_start.bat

### Bước 3: Tạo cơ sở dữ liệu

- Tạo database mới, ví dụ: `tech_store`
- Import 2 file SQL:
  - Cấu trúc: `database/tech_store.sql`
  - Dữ liệu mẫu: `database/data.sql`

Import bằng phpMyAdmin hoặc dòng lệnh:
```bash
mysql -u root -p tech_store < database/schema.sql
mysql -u root -p tech_store < database/data.sql
```

### Bước 4: Cấu hình kết nối database

Mở file `config/database.php` và sửa thông tin kết nối cho phù hợp:
```php
define('DB_HOST', '103.200.23.120');
define('DB_USER', 'nrojunec_store');
define('DB_PASS', '');
define('DB_NAME', 'nrojunec_store');
```

### Bước 5: Phân quyền thư mục uploads

Đảm bảo thư mục `uploads/` tồn tại ở gốc dự án và web server có quyền ghi vào đây (dùng để lưu ảnh sản phẩm, danh mục).

## 3. Cấu trúc thư mục

```
/
├── admin/                # Trang quản trị (sản phẩm, đơn hàng, người dùng, cài đặt)
├── assets/
│   ├── css/              # File style.css (tùy chỉnh giao diện)
│   ├── js/               # File main.js, search-autocomplete.js
│   └── images/           # Ảnh giao diện, banner, v.v.
├── auth/                 # Đăng nhập Google, Facebook
├── config/               # Cấu hình database
├── database/             # File SQL tạo bảng và dữ liệu mẫu
├── includes/             # Header, footer, navigation, functions...
├── uploads/              # Ảnh sản phẩm, danh mục (tự động tạo nếu chưa có)
├── index.php             # Trang chủ
├── product.php           # Trang chi tiết sản phẩm
├── category.php          # Trang danh mục sản phẩm
├── cart.php              # Giỏ hàng
├── checkout.php          # Thanh toán
├── account.php           # Tài khoản người dùng
├── register.php          # Đăng ký
├── login.php             # Đăng nhập
├── README.md
└── ...
```

## 4. Hướng dẫn sử dụng

### Người dùng

- Xem sản phẩm, lọc theo danh mục, giá, thương hiệu.
- Đăng ký, đăng nhập (bằng email hoặc Google/Facebook).
- Thêm sản phẩm vào giỏ hàng, đặt hàng, xem lịch sử đơn hàng.

### Quản trị viên/nhân viên

- Đăng nhập tài khoản admin/staff.
- Quản lý sản phẩm, danh mục, đơn hàng, người dùng tại thư mục `/admin`.
- Thêm/sửa/xóa sản phẩm, upload ảnh (ảnh sẽ lưu vào `uploads/` ở gốc dự án).
- Quản lý trạng thái đơn hàng, xem doanh thu, thống kê.

## 5. Đăng nhập Google & Facebook

- Cài đặt Google/Facebook App, lấy Client ID/Secret.
- Sửa các thông tin sau trong file:
  - `auth/google_login.php`
  - `auth/facebook_login.php`
- Cài đặt callback URL đúng với domain của bạn.
- Cài đặt các package qua Composer (đã có trong composer.json):
  - `google/apiclient`
  - `facebook/graph-sdk`

## 6. Tùy chỉnh giao diện

- Sửa file `assets/css/style.css` để thay đổi màu sắc, bố cục.
- Thay ảnh banner ở `assets/images/hero1.jpg`, `hero2.jpg`...

## 7. Một số lưu ý

- Khi upload ảnh, đường dẫn lưu vào database là dạng: `uploads/tenfile.jpg`
- Khi hiển thị ảnh, chỉ cần dùng:  
  ```php
  <img src="<?php echo htmlspecialchars($product['image']); ?>">
  ```
- Nếu upload từ trang admin, ảnh vẫn sẽ nằm ở `uploads/` gốc dự án.

## 8. Tài khoản mẫu

- Admin:  
  - Email: `admin@gmail.com`  
  - Mật khẩu: `123456`
- Nhân viên:  
  - Email: `nhanvien@example.com`  
  - Mật khẩu: `password`
- Khách hàng:  
  - Email: `khachhang@example.com`  
  - Mật khẩu: `password`


---