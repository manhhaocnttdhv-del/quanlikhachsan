# 🏨 HỆ THỐNG QUẢN LÝ KHÁCH SẠN

Hệ thống quản lý khách sạn toàn diện với đầy đủ chức năng cho cả người dùng và quản trị viên.

## ✨ TÍNH NĂNG CHÍNH

### 👤 Dành cho Người dùng:
- ✅ Đăng ký / Đăng nhập
- 👤 Quản lý thông tin cá nhân
- 🔍 Tìm kiếm & xem phòng
- 📅 Đặt phòng
- 📋 Quản lý đặt phòng
- 💳 Thanh toán

### 🔧 Dành cho Admin:
- 🏠 Quản lý phòng
- 👥 Quản lý khách hàng
- 📊 Quản lý đặt phòng
- 💰 Quản lý hóa đơn / thanh toán
- 👔 Quản lý nhân viên

## 🚀 CÀI ĐẶT NHANH

### 1️⃣ Clone project
```bash
cd C:\laragon\www\quanlikhachsan
```

### 2️⃣ Cấu hình .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quanlikhachsan
DB_USERNAME=root
DB_PASSWORD=
```

### 3️⃣ Chạy setup (Windows)
```bash
setup.bat
```

**HOẶC** chạy thủ công:
```bash
php artisan migrate:fresh
php artisan db:seed
php artisan storage:link
php artisan serve
```

### 4️⃣ Truy cập hệ thống
- **Website:** http://localhost:8000
- **Admin:** http://localhost:8000/admin/login

## 🔐 TÀI KHOẢN MẶC ĐỊNH

| Vai trò | Email | Mật khẩu |
|---------|-------|----------|
| Admin | admin@hotel.com | admin123 |
| Manager | manager@hotel.com | manager123 |

## 📁 CẤU TRÚC PROJECT

```
quanlikhachsan/
├── app/
│   ├── Http/Controllers/
│   │   ├── User/           # Controllers cho người dùng
│   │   └── Admin/          # Controllers cho admin
│   └── Models/             # Eloquent Models
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   └── views/
│       ├── layouts/        # Layout chung
│       ├── user/           # Views cho người dùng
│       └── admin/          # Views cho admin
├── routes/
│   └── web.php             # Định nghĩa routes
├── setup.bat               # Script cài đặt tự động
└── HUONG_DAN.md           # Hướng dẫn chi tiết
```

## 💾 DATABASE

Hệ thống sử dụng 5 bảng chính:
- `users` - Thông tin người dùng/khách hàng
- `admins` - Thông tin quản trị viên/nhân viên
- `rooms` - Thông tin phòng
- `bookings` - Thông tin đặt phòng
- `payments` - Thông tin thanh toán

## 🛠️ CÔNG NGHỆ

- **Framework:** Laravel 10.x
- **PHP:** >= 8.1
- **Database:** MySQL/MariaDB
- **Frontend:** Bootstrap 5.3, Font Awesome 6.4
- **Authentication:** Laravel Multi-Guard

## 📖 HƯỚNG DẪN SỬ DỤNG

Chi tiết xem file [HUONG_DAN.md](HUONG_DAN.md)

## 📸 SCREENSHOTS

### Trang chủ
- Hiển thị phòng nổi bật
- Tìm kiếm phòng dễ dàng

### Admin Dashboard
- Thống kê tổng quan
- Doanh thu
- Quản lý toàn diện

## 🎯 YÊU CẦU HỆ THỐNG

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Laravel 10.x
- Extension: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON

## 🔄 CẬP NHẬT

Để cập nhật database với dữ liệu mới:
```bash
php artisan migrate:fresh --seed
```

⚠️ **Chú ý:** Lệnh này sẽ xóa toàn bộ dữ liệu hiện có!

## 📞 HỖ TRỢ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra PHP version
2. Kiểm tra database connection
3. Xóa cache: `php artisan cache:clear`
4. Xem log tại `storage/logs/laravel.log`

## 📝 LICENSE

Dự án học tập - Không giới hạn sử dụng

---

**Phát triển bởi:** Hotel Management Team  
**Phiên bản:** 1.0.0  
**Ngày cập nhật:** {{ date('Y-m-d') }}
