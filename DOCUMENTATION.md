# 📚 TÀI LIỆU HỆ THỐNG QUẢN LÝ KHÁCH SẠN

## MỤC LỤC

1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Kiến trúc hệ thống](#2-kiến-trúc-hệ-thống)
   - [Mô hình MVC](#21-mô-hình-mvc-model-view-controller)
   - [Luồng xử lý Request trong Laravel](#22-luồng-xử-lý-request-trong-laravel)
   - [Tương tác giữa các thành phần MVC](#24-tương-tác-giữa-các-thành-phần-mvc)
   - [Ví dụ luồng hoàn chỉnh](#26-ví-dụ-luồng-hoàn-chỉnh-đặt-phòng-và-thanh-toán)
3. [Cấu trúc Database](#3-cấu-trúc-database)
4. [Nghiệp vụ chính](#4-nghiệp-vụ-chính)
5. [Luồng xử lý nghiệp vụ](#5-luồng-xử-lý-nghiệp-vụ)
6. [Cấu trúc Code](#6-cấu-trúc-code)
7. [Models và Relationships](#7-models-và-relationships)
8. [Controllers và Logic](#8-controllers-và-logic)
9. [Authentication & Authorization](#9-authentication--authorization)
10. [Tính năng đặc biệt](#10-tính-năng-đặc-biệt)

---

## 1. TỔNG QUAN HỆ THỐNG

### 1.1. Mục đích
Hệ thống quản lý khách sạn toàn diện cho phép:
- **Người dùng (User)**: Tìm kiếm, xem, đặt phòng, thanh toán và đánh giá
- **Quản trị viên (Admin)**: Quản lý phòng, khách hàng, đặt phòng, thanh toán, nhân viên

### 1.2. Công nghệ sử dụng
- **Backend**: Laravel 10.x
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.3, Font Awesome 6.4
- **Authentication**: Laravel Multi-Guard (User & Admin)
- **Payment**: VNPay, QR Chuyển khoản
- **Export/Import**: Maatwebsite Excel
- **PDF**: DomPDF

### 1.3. Cấu trúc thư mục chính
```
app/
├── Http/Controllers/
│   ├── User/          # Controllers cho người dùng
│   └── Admin/         # Controllers cho admin
├── Models/            # Eloquent Models
├── Services/          # Business logic services
├── Exports/           # Excel export classes
├── Imports/           # Excel import classes
└── Mail/              # Email classes

database/
├── migrations/        # Database schema
└── seeders/           # Sample data

resources/views/
├── layouts/           # Layout templates
├── user/              # Views cho người dùng
└── admin/             # Views cho admin
```

---

## 2. KIẾN TRÚC HỆ THỐNG

### 2.1. Mô hình MVC (Model-View-Controller)

#### 2.1.1. Khái niệm MVC

**MVC** là mô hình kiến trúc phần mềm chia ứng dụng thành 3 thành phần:

1. **Model (M)**: 
   - Đại diện cho dữ liệu và business logic
   - Tương tác với database
   - Xử lý validation, relationships, scopes
   - **Ví dụ**: `User`, `Room`, `Booking`, `Payment`, `Review`

2. **View (V)**:
   - Hiển thị giao diện người dùng
   - Nhận dữ liệu từ Controller
   - Không chứa business logic
   - **Ví dụ**: Blade templates trong `resources/views/`

3. **Controller (C)**:
   - Xử lý HTTP requests
   - Điều phối giữa Model và View
   - Xử lý validation, authentication, authorization
   - **Ví dụ**: `BookingController`, `PaymentController`

#### 2.1.2. Luồng xử lý MVC trong Laravel

```
┌─────────────┐
│   Request   │ (HTTP Request từ Browser)
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│         Routes (web.php)            │ ← Định nghĩa URL → Controller mapping
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│      Middleware Stack               │ ← Authentication, CSRF, etc.
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│      Controller                     │ ← Xử lý logic nghiệp vụ
│  (BookingController@store)          │
└──────┬──────────────────────────────┘
       │
       ├─────────────────┬─────────────┐
       ▼                 ▼             ▼
┌──────────┐    ┌──────────────┐  ┌──────────┐
│  Model   │    │  Validation  │  │ Service │
│ (Booking)│    │   Request    │  │ (VNPay) │
└────┬─────┘    └──────────────┘  └──────────┘
     │
     ▼
┌─────────────┐
│  Database  │ (MySQL)
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│      Controller                     │ ← Nhận dữ liệu từ Model
│  (Trả về View hoặc Redirect)        │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────┐
│    View     │ ← Render HTML với dữ liệu
│ (Blade)     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Response   │ (HTTP Response về Browser)
└─────────────┘
```

#### 2.1.3. Ví dụ cụ thể: Tạo Đặt Phòng

**1. Request từ Browser**:
```
POST /bookings
Content-Type: application/x-www-form-urlencoded

room_id=1&check_in_date=2024-01-15&check_out_date=2024-01-17&number_of_guests=2
```

**2. Route định nghĩa** (`routes/web.php`):
```php
Route::middleware('auth')->prefix('bookings')->name('user.bookings.')->group(function () {
    Route::post('/', [BookingController::class, 'store'])->name('store');
});
```

**3. Middleware xử lý**:
```php
// app/Http/Middleware/Authenticate.php
- Kiểm tra user đã đăng nhập chưa
- Nếu chưa → Redirect về login
- Nếu có → Tiếp tục
```

**4. Controller xử lý** (`app/Http/Controllers/User/BookingController.php`):
```php
public function store(Request $request)
{
    // 1. VALIDATION
    $validated = $request->validate([
        'room_id' => 'required|exists:rooms,id',
        'check_in_date' => 'required|date|after_or_equal:today',
        'check_out_date' => 'required|date|after:check_in_date',
        'number_of_guests' => 'required|integer|min:1',
    ]);

    // 2. LẤY DỮ LIỆU TỪ MODEL
    $room = Room::findOrFail($validated['room_id']);

    // 3. BUSINESS LOGIC
    $checkIn = Carbon::parse($validated['check_in_date']);
    $checkOut = Carbon::parse($validated['check_out_date']);
    $nights = $checkIn->diffInDays($checkOut);
    $totalPrice = $nights * $room->price_per_night;

    // 4. KIỂM TRA NGHIỆP VỤ
    $overlappingBooking = Booking::overlapping(...)->first();
    if ($overlappingBooking) {
        return back()->withErrors([...]);
    }

    // 5. TẠO DỮ LIỆU QUA MODEL
    $booking = Booking::create([
        'user_id' => Auth::id(),
        'room_id' => $validated['room_id'],
        'check_in_date' => $validated['check_in_date'],
        'check_out_date' => $validated['check_out_date'],
        'number_of_guests' => $validated['number_of_guests'],
        'total_price' => $totalPrice,
        'status' => 'pending',
    ]);

    // 6. TRẢ VỀ RESPONSE (Redirect đến View)
    return redirect()->route('user.bookings.show', $booking->id)
        ->with('success', 'Đặt phòng thành công!');
}
```

**5. Model xử lý** (`app/Models/Booking.php`):
```php
// Eloquent tự động:
- Insert vào database
- Fill các trường từ $fillable
- Tự động set timestamps
- Trigger relationships
```

**6. Response về Browser**:
```
HTTP/1.1 302 Found
Location: /bookings/123
Set-Cookie: laravel_session=...
```

**7. Browser redirect đến View** (`resources/views/user/bookings/show.blade.php`):
```blade
@extends('layouts.app')

@section('content')
    <h1>Chi tiết đặt phòng #{{ $booking->id }}</h1>
    <p>Phòng: {{ $booking->room->room_number }}</p>
    <p>Tổng tiền: {{ number_format($booking->total_price) }} VNĐ</p>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
@endsection
```

**8. Render HTML và trả về Browser**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Chi tiết đặt phòng</title>
</head>
<body>
    <h1>Chi tiết đặt phòng #123</h1>
    <p>Phòng: 101 - Standard</p>
    <p>Tổng tiền: 1,000,000 VNĐ</p>
    <div class="alert alert-success">Đặt phòng thành công!</div>
</body>
</html>
```

### 2.2. Luồng xử lý Request trong Laravel

#### 2.2.1. Lifecycle của Request

```
1. public/index.php
   ↓
2. bootstrap/app.php (Khởi tạo Application)
   ↓
3. HTTP Kernel (app/Http/Kernel.php)
   ↓
4. Middleware Stack
   ├── StartSession
   ├── ShareErrorsFromSession
   ├── VerifyCsrfToken
   ├── Authenticate (nếu cần)
   └── ...
   ↓
5. Router (routes/web.php)
   ↓
6. Controller Method
   ↓
7. Model (Eloquent ORM)
   ↓
8. Database Query
   ↓
9. Response
   ↓
10. Middleware (Terminate)
   ↓
11. Browser nhận Response
```

#### 2.2.2. Chi tiết từng bước

**Bước 1: Entry Point** (`public/index.php`)
```php
// 1. Load Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// 2. Bootstrap Laravel application
$app = require_once __DIR__.'/../bootstrap/app.php';

// 3. Create HTTP Kernel
$kernel = $app->make(Kernel::class);

// 4. Handle request
$response = $kernel->handle(
    $request = Request::capture()
)->send();

// 5. Terminate (cleanup)
$kernel->terminate($request, $response);
```

**Bước 2: Bootstrap** (`bootstrap/app.php`)
```php
// Khởi tạo Application container
// Load service providers
// Register bindings
```

**Bước 3: HTTP Kernel** (`app/Http/Kernel.php`)
```php
protected $middleware = [
    // Global middleware - chạy cho mọi request
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
];

protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

**Bước 4: Route Matching** (`routes/web.php`)
```php
// Laravel tìm route khớp với request
Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('auth')
    ->name('user.bookings.store');

// Nếu match → Gọi Controller
// Nếu không match → 404 Not Found
```

**Bước 5: Middleware Execution**
```php
// Chạy middleware theo thứ tự:
1. StartSession → Bắt đầu session
2. ShareErrorsFromSession → Share errors cho view
3. VerifyCsrfToken → Kiểm tra CSRF token
4. Authenticate → Kiểm tra đăng nhập
   - Nếu chưa đăng nhập → Redirect về login
   - Nếu đã đăng nhập → Tiếp tục
```

**Bước 6: Controller Method**
```php
// Controller nhận Request object
public function store(Request $request)
{
    // Xử lý logic...
    return redirect()->route('...');
}
```

**Bước 7: Model & Database**
```php
// Eloquent ORM xử lý query
$booking = Booking::create([...]);

// Tạo SQL query:
// INSERT INTO bookings (user_id, room_id, ...) VALUES (?, ?, ...)

// Execute query
// Trả về Model instance
```

**Bước 8: Response**
```php
// Controller trả về Response object
return redirect()->route('user.bookings.show', $booking->id)
    ->with('success', 'Đặt phòng thành công!');

// Laravel convert thành HTTP Response:
// HTTP/1.1 302 Found
// Location: /bookings/123
// Set-Cookie: laravel_session=...
```

#### 2.2.3. Ví dụ luồng xử lý: Thanh toán QR

**Request**:
```
POST /payments
Content-Type: application/x-www-form-urlencoded
Cookie: laravel_session=abc123

booking_id=123&payment_method=bank_transfer_qr
```

**Luồng xử lý**:

```
1. public/index.php
   └─> Request::capture()
       └─> Method: POST
       └─> URI: /payments
       └─> Body: booking_id=123&payment_method=bank_transfer_qr

2. bootstrap/app.php
   └─> Khởi tạo Application
   └─> Load Service Providers

3. app/Http/Kernel.php
   └─> Middleware Stack:
       ├─> StartSession
       │   └─> Load session từ cookie
       ├─> ShareErrorsFromSession
       ├─> VerifyCsrfToken
       │   └─> Kiểm tra CSRF token trong form
       └─> Authenticate
           └─> Kiểm tra user đã login
           └─> Load user từ session

4. routes/web.php
   └─> Tìm route match: POST /payments
   └─> Route::post('/payments', [PaymentController::class, 'store'])
       └─> Middleware: auth

5. app/Http/Controllers/User/PaymentController.php
   └─> Method: store(Request $request)
       ├─> Validate request
       │   └─> booking_id: required|exists:bookings,id
       │   └─> payment_method: required|in:bank_transfer_qr
       │
       ├─> Lấy Booking từ Model
       │   └─> $booking = Booking::findOrFail($booking_id)
       │   └─> SQL: SELECT * FROM bookings WHERE id = 123
       │
       ├─> Kiểm tra quyền
       │   └─> if ($booking->user_id !== Auth::id()) abort(403)
       │
       ├─> Business Logic
       │   └─> Tạo Payment
       │   └─> Payment::updateOrCreate([...])
       │   └─> SQL: INSERT INTO payments (...) VALUES (...)
       │
       └─> Response
           └─> redirect()->route('user.payments.qr', $payment->id)

6. Response về Browser
   └─> HTTP/1.1 302 Found
   └─> Location: /payments/qr/456
   └─> Set-Cookie: laravel_session=abc123

7. Browser tự động redirect
   └─> GET /payments/qr/456

8. Lặp lại từ bước 1 với request mới
   └─> PaymentController@showQR
   └─> Render view: user.payments.qr
   └─> Trả về HTML với QR code
```

### 2.3. Multi-Guard Authentication
Hệ thống sử dụng 2 guards riêng biệt:
- **`web`**: Cho người dùng thông thường (User)
- **`admin`**: Cho quản trị viên (Admin)

### 2.4. Tương tác giữa các thành phần MVC

#### 2.4.1. Model ↔ Controller

**Controller sử dụng Model**:
```php
// app/Http/Controllers/User/BookingController.php
public function store(Request $request)
{
    // 1. Lấy dữ liệu từ Model
    $room = Room::findOrFail($request->room_id);
    
    // 2. Sử dụng Model relationships
    $user = Auth::user(); // User Model
    $bookings = $user->bookings; // Lấy bookings qua relationship
    
    // 3. Sử dụng Model scopes
    $availableRooms = Room::available()->get();
    
    // 4. Tạo mới qua Model
    $booking = Booking::create([
        'user_id' => $user->id,
        'room_id' => $room->id,
        // ...
    ]);
    
    // 5. Cập nhật qua Model
    $booking->update(['status' => 'confirmed']);
    
    // 6. Xóa qua Model
    $booking->delete();
}
```

**Model cung cấp dữ liệu cho Controller**:
```php
// app/Models/Booking.php
class Booking extends Model
{
    // Relationships - Controller có thể dùng
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    // Scopes - Controller có thể dùng
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
    
    // Accessors - Tự động format khi lấy dữ liệu
    public function getFormattedPriceAttribute() {
        return number_format($this->total_price) . ' VNĐ';
    }
}
```

#### 2.4.2. Controller ↔ View

**Controller truyền dữ liệu cho View**:
```php
// app/Http/Controllers/User/BookingController.php
public function show($id)
{
    // 1. Lấy dữ liệu từ Model
    $booking = Booking::with(['room', 'payment', 'review'])
        ->findOrFail($id);
    
    // 2. Truyền dữ liệu cho View
    return view('user.bookings.show', compact('booking'));
    
    // Hoặc
    return view('user.bookings.show', [
        'booking' => $booking,
        'room' => $booking->room,
    ]);
}
```

**View nhận và hiển thị dữ liệu**:
```blade
{{-- resources/views/user/bookings/show.blade.php --}}
@extends('layouts.app')

@section('content')
    {{-- Truy cập dữ liệu từ Controller --}}
    <h1>Đặt phòng #{{ $booking->id }}</h1>
    
    {{-- Sử dụng relationships --}}
    <p>Phòng: {{ $booking->room->room_number }}</p>
    <p>Khách hàng: {{ $booking->user->name }}</p>
    
    {{-- Sử dụng accessors --}}
    <p>Giá: {{ $booking->formatted_price }}</p>
    
    {{-- Kiểm tra điều kiện --}}
    @if($booking->payment)
        <p>Trạng thái thanh toán: {{ $booking->payment->payment_status }}</p>
    @endif
    
    {{-- Session messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
@endsection
```

#### 2.4.3. View → Controller (Form Submission)

**View tạo form**:
```blade
{{-- resources/views/user/bookings/create.blade.php --}}
<form action="{{ route('user.bookings.store') }}" method="POST">
    @csrf {{-- CSRF token --}}
    
    <input type="hidden" name="room_id" value="{{ $room->id }}">
    
    <input type="date" name="check_in_date" required>
    <input type="date" name="check_out_date" required>
    <input type="number" name="number_of_guests" required>
    
    <button type="submit">Đặt phòng</button>
</form>
```

**Controller nhận request**:
```php
// app/Http/Controllers/User/BookingController.php
public function store(Request $request)
{
    // Request chứa dữ liệu từ form
    $roomId = $request->input('room_id');
    $checkIn = $request->input('check_in_date');
    $checkOut = $request->input('check_out_date');
    
    // Validate
    $validated = $request->validate([...]);
    
    // Xử lý và lưu vào Model
    $booking = Booking::create($validated);
    
    // Redirect về View khác
    return redirect()->route('user.bookings.show', $booking->id);
}
```

#### 2.4.4. Model ↔ Database

**Eloquent ORM tự động chuyển đổi**:
```php
// Model method
$booking = Booking::create([
    'user_id' => 1,
    'room_id' => 2,
    'check_in_date' => '2024-01-15',
    'total_price' => 1000000,
]);

// Eloquent tự động tạo SQL:
// INSERT INTO bookings (user_id, room_id, check_in_date, total_price, created_at, updated_at)
// VALUES (1, 2, '2024-01-15', 1000000, NOW(), NOW())

// Query Builder
$bookings = Booking::where('status', 'pending')
    ->where('user_id', Auth::id())
    ->orderBy('created_at', 'desc')
    ->get();

// SQL:
// SELECT * FROM bookings 
// WHERE status = 'pending' AND user_id = 1 
// ORDER BY created_at DESC
```

### 2.5. Middleware
- `auth`: Bảo vệ routes cho User
- `auth:admin`: Bảo vệ routes cho Admin
- `role.admin:admin`: Chỉ Admin mới có quyền (Manager không có)

### 2.6. Ví dụ luồng hoàn chỉnh: Đặt phòng và Thanh toán

#### 2.6.1. Bước 1: User xem danh sách phòng

```
Request: GET /rooms
  ↓
Route: Route::get('/rooms', [RoomController::class, 'index'])
  ↓
Controller: RoomController@index
  ├─> Model: Room::available()->paginate(10)
  │   └─> SQL: SELECT * FROM rooms WHERE status = 'available' LIMIT 10
  └─> View: return view('user.rooms.index', compact('rooms'))
      └─> Render: resources/views/user/rooms/index.blade.php
          └─> HTML: <div>Phòng 101</div><div>Phòng 102</div>...
```

#### 2.6.2. Bước 2: User xem chi tiết phòng

```
Request: GET /rooms/1
  ↓
Route: Route::get('/rooms/{id}', [RoomController::class, 'show'])
  ↓
Controller: RoomController@show($id)
  ├─> Model: Room::with(['images', 'reviews'])->findOrFail($id)
  │   ├─> SQL: SELECT * FROM rooms WHERE id = 1
  │   ├─> SQL: SELECT * FROM room_images WHERE room_id = 1
  │   └─> SQL: SELECT * FROM reviews WHERE room_id = 1 AND status = 'approved'
  └─> View: return view('user.rooms.show', compact('room'))
      └─> Render: resources/views/user/rooms/show.blade.php
          └─> HTML: <h1>Phòng 101</h1><img src="..."><button>Đặt phòng</button>
```

#### 2.6.3. Bước 3: User click "Đặt phòng"

```
Request: GET /bookings/create?room_id=1
  ↓
Route: Route::get('/bookings/create', [BookingController::class, 'create'])
  ↓
Middleware: auth (kiểm tra đăng nhập)
  ├─> Nếu chưa login → Redirect /user/login
  └─> Nếu đã login → Tiếp tục
  ↓
Controller: BookingController@create
  ├─> Model: Room::findOrFail($request->room_id)
  │   └─> SQL: SELECT * FROM rooms WHERE id = 1
  └─> View: return view('user.bookings.create', compact('room'))
      └─> Render: resources/views/user/bookings/create.blade.php
          └─> HTML: <form>...</form> (Form đặt phòng)
```

#### 2.6.4. Bước 4: User submit form đặt phòng

```
Request: POST /bookings
  Body: room_id=1&check_in_date=2024-01-15&check_out_date=2024-01-17&number_of_guests=2
  ↓
Route: Route::post('/bookings', [BookingController::class, 'store'])
  ↓
Middleware: auth, VerifyCsrfToken
  ↓
Controller: BookingController@store(Request $request)
  ├─> Validation: $request->validate([...])
  │   └─> Nếu lỗi → Redirect back với errors
  │
  ├─> Model: Room::findOrFail($roomId)
  │   └─> SQL: SELECT * FROM rooms WHERE id = 1
  │
  ├─> Business Logic:
  │   ├─> Tính số đêm: $nights = diffInDays(check_in, check_out)
  │   ├─> Tính giá: $totalPrice = $nights * $room->price_per_night
  │   └─> Kiểm tra trùng lịch: Booking::overlapping(...)
  │       └─> SQL: SELECT * FROM bookings WHERE room_id = 1 
  │                AND check_in_date < '2024-01-17' 
  │                AND check_out_date > '2024-01-15'
  │                AND status IN ('pending', 'confirmed', 'checked_in')
  │
  ├─> Model: Booking::create([...])
  │   └─> SQL: INSERT INTO bookings (...) VALUES (...)
  │
  └─> Response: redirect()->route('user.bookings.show', $booking->id)
      └─> HTTP 302 → Location: /bookings/123
```

#### 2.6.5. Bước 5: User xem chi tiết đặt phòng

```
Request: GET /bookings/123
  ↓
Controller: BookingController@show(123)
  ├─> Model: Booking::with(['room', 'payment', 'review'])->findOrFail(123)
  │   └─> SQL: SELECT * FROM bookings WHERE id = 123
  │   └─> SQL: SELECT * FROM rooms WHERE id IN (1)
  │   └─> SQL: SELECT * FROM payments WHERE booking_id = 123
  │
  └─> View: return view('user.bookings.show', compact('booking'))
      └─> Render: resources/views/user/bookings/show.blade.php
          └─> HTML: <h1>Đặt phòng #123</h1>
                   <p>Phòng: 101</p>
                   <p>Tổng tiền: 1,000,000 VNĐ</p>
                   <a href="/payments/booking/123">Thanh toán</a>
```

#### 2.6.6. Bước 6: User chọn thanh toán

```
Request: GET /payments/booking/123
  ↓
Controller: PaymentController@create(123)
  ├─> Model: Booking::with('room')->findOrFail(123)
  │   └─> SQL: SELECT * FROM bookings WHERE id = 123
  │
  └─> View: return view('user.payments.create', compact('booking'))
      └─> Render: resources/views/user/payments/create.blade.php
          └─> HTML: <form>
                   <input type="radio" name="payment_method" value="bank_transfer_qr">
                   <button>Thanh toán</button>
                   </form>
```

#### 2.6.7. Bước 7: User submit thanh toán

```
Request: POST /payments
  Body: booking_id=123&payment_method=bank_transfer_qr
  ↓
Controller: PaymentController@store(Request $request)
  ├─> Validation: $request->validate([...])
  │
  ├─> Model: Booking::findOrFail(123)
  │   └─> SQL: SELECT * FROM bookings WHERE id = 123
  │
  ├─> Model: Payment::updateOrCreate([...])
  │   └─> SQL: INSERT INTO payments (...) VALUES (...)
  │
  └─> Response: redirect()->route('user.payments.qr', $payment->id)
      └─> HTTP 302 → Location: /payments/qr/456
```

#### 2.6.8. Bước 8: User xem QR code

```
Request: GET /payments/qr/456
  ↓
Controller: PaymentController@showQR(456)
  ├─> Model: Payment::with(['booking.user', 'booking.room'])->findOrFail(456)
  │   └─> SQL: SELECT * FROM payments WHERE id = 456
  │   └─> SQL: SELECT * FROM bookings WHERE id = 123
  │   └─> SQL: SELECT * FROM users WHERE id = 1
  │   └─> SQL: SELECT * FROM rooms WHERE id = 1
  │
  ├─> Business Logic:
  │   ├─> Lấy thông tin ngân hàng từ ENV
  │   ├─> Tạo nội dung chuyển khoản
  │   ├─> Loại bỏ dấu tiếng Việt
  │   └─> Tạo QR data string
  │
  └─> View: return view('user.payments.qr', compact(...))
      └─> Render: resources/views/user/payments/qr.blade.php
          ├─> Generate QR code: QrCode::generate($qrData)
          └─> HTML: <div>QR Code</div>
                   <p>Số TK: 1234567890</p>
                   <p>Số tiền: 1,000,000 VNĐ</p>
                   <form>Xác nhận đã chuyển khoản</form>
```

### 2.7. Tóm tắt luồng MVC

```
┌─────────────────────────────────────────────────────────────┐
│                    REQUEST CYCLE                            │
└─────────────────────────────────────────────────────────────┘

1. HTTP Request
   ↓
2. Route Matching (routes/web.php)
   ↓
3. Middleware Stack (app/Http/Kernel.php)
   ↓
4. Controller Method (app/Http/Controllers/...)
   │
   ├─→ Validation (Request)
   │
   ├─→ Business Logic
   │   │
   │   ├─→ Model Queries (app/Models/...)
   │   │   └─→ Database (MySQL)
   │   │
   │   └─→ Services (app/Services/...)
   │
   └─→ Response
       │
       ├─→ View (resources/views/...)
       │   └─→ Render HTML
       │
       └─→ Redirect/JSON/File Download
           ↓
5. HTTP Response
   ↓
6. Browser
```

**Nguyên tắc**:
- **Model**: Chỉ xử lý dữ liệu, không biết về HTTP
- **View**: Chỉ hiển thị, không có business logic
- **Controller**: Điều phối, không chứa logic phức tạp (nên đưa vào Service)
- **Separation of Concerns**: Mỗi thành phần có trách nhiệm riêng

---

## 3. CẤU TRÚC DATABASE

### 3.1. Bảng `users` (Khách hàng)
```sql
- id (PK)
- name
- email (unique)
- password (hashed)
- phone
- address
- cccd (Căn cước công dân)
- birth_date
- email_verified_at
- remember_token
- created_at, updated_at
```

**Quan hệ**:
- `hasMany` Bookings
- `hasMany` Reviews

### 3.2. Bảng `admins` (Quản trị viên/Nhân viên)
```sql
- id (PK)
- name
- email (unique)
- password (hashed)
- phone
- role (enum: 'admin', 'manager')
- email_verified_at
- remember_token
- created_at, updated_at
```

**Quan hệ**: Không có quan hệ với các bảng khác

**Phân quyền**:
- **Admin**: Toàn quyền (quản lý nhân viên, export dữ liệu)
- **Manager**: Quyền hạn chế (quản lý phòng, đặt phòng, thanh toán, khách hàng, đánh giá)

### 3.3. Bảng `rooms` (Phòng)
```sql
- id (PK)
- room_number (unique)
- room_type (Standard, Deluxe, Suite, VIP)
- capacity (Số người tối đa)
- price_per_night (decimal 10,2)
- description (text)
- amenities (json) - Tiện nghi: TV, WiFi, máy lạnh, etc.
- image (string) - Ảnh chính (deprecated, dùng room_images)
- status (enum: 'available', 'occupied', 'maintenance')
- created_at, updated_at
```

**Quan hệ**:
- `hasMany` Bookings
- `hasMany` RoomImages
- `hasMany` Reviews

**Scopes**:
- `available()`: Lọc phòng có sẵn
- `byType($type)`: Lọc theo loại phòng

### 3.4. Bảng `room_images` (Ảnh phòng)
```sql
- id (PK)
- room_id (FK -> rooms.id)
- image_path
- order (Thứ tự hiển thị)
- is_primary (boolean) - Ảnh chính
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo` Room

### 3.5. Bảng `bookings` (Đặt phòng)
```sql
- id (PK)
- user_id (FK -> users.id)
- room_id (FK -> rooms.id)
- check_in_date (date)
- check_out_date (date)
- number_of_guests (integer)
- total_price (decimal 10,2)
- status (enum: 'pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled')
- special_requests (text, nullable)
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo` User
- `belongsTo` Room
- `hasOne` Payment
- `hasOne` Review

**Scopes**:
- `pending()`: Đặt phòng chờ xác nhận
- `confirmed()`: Đặt phòng đã xác nhận
- `active()`: Đặt phòng đang hoạt động (confirmed, checked_in)
- `overlapping($roomId, $checkIn, $checkOut, $excludeId)`: Kiểm tra trùng lịch

**Logic trùng lịch**:
- Hai booking overlap nếu: `check_in_date mới < check_out_date cũ` VÀ `check_out_date mới > check_in_date cũ`
- Chỉ kiểm tra với status: `pending`, `confirmed`, `checked_in`

### 3.6. Bảng `payments` (Thanh toán)
```sql
- id (PK)
- booking_id (FK -> bookings.id, unique)
- amount (decimal 10,2)
- payment_method (enum: 'cash', 'credit_card', 'bank_transfer', 'momo', 'vnpay', 'bank_transfer_qr')
- payment_status (enum: 'pending', 'completed', 'failed', 'refunded')
- payment_date (datetime, nullable)
- transaction_id (string, nullable)
- notes (text, nullable)
- receipt_image (string, nullable) - Ảnh biên lai chuyển khoản
- bank_transfer_qr (string, nullable) - QR code chuyển khoản
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo` Booking

**Scopes**:
- `completed()`: Thanh toán đã hoàn thành
- `pending()`: Thanh toán chờ xử lý

### 3.7. Bảng `reviews` (Đánh giá)
```sql
- id (PK)
- user_id (FK -> users.id)
- room_id (FK -> rooms.id)
- booking_id (FK -> bookings.id)
- rating (integer 1-5)
- comment (text)
- status (enum: 'pending', 'approved', 'rejected')
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo` User
- `belongsTo` Room
- `belongsTo` Booking

**Scopes**:
- `approved()`: Đánh giá đã được duyệt
- `pending()`: Đánh giá chờ duyệt
- `byRoom($roomId)`: Lọc theo phòng

---

## 4. NGHIỆP VỤ CHÍNH

### 4.1. Quản lý Phòng (Admin)

#### 4.1.1. CRUD Phòng
- **Tạo phòng**: Nhập thông tin, upload ảnh (nhiều ảnh), chọn ảnh chính
- **Sửa phòng**: Cập nhật thông tin, thêm/xóa ảnh, đổi ảnh chính
- **Xóa phòng**: Xóa phòng và tất cả ảnh liên quan
- **Xem danh sách**: Phân trang, lọc theo trạng thái

#### 4.1.2. Import/Export Phòng
- **Import từ Excel**: Upload file Excel/CSV để thêm nhiều phòng cùng lúc
- **Export template**: Tải file mẫu để điền thông tin
- **Export phòng đã scrape**: Xuất dữ liệu phòng đã lấy từ website khác

#### 4.1.3. Scrape Phòng từ Website
- Hỗ trợ các website: Booking.com, Agoda, Expedia, Hotels.com, Traveloka, VnTravel, Mytour.vn, Luxstay
- Tự động trích xuất: Tên phòng, loại phòng, giá, mô tả, tiện nghi, ảnh
- Tải ảnh về server và lưu vào database

**Quy trình**:
1. Admin nhập URL trang web có thông tin phòng
2. Hệ thống detect loại website
3. Fetch HTML với headers phù hợp
4. Parse HTML/JSON để trích xuất thông tin phòng
5. Download ảnh về server
6. Lưu vào database

### 4.2. Đặt Phòng (User)

#### 4.2.1. Tìm kiếm và Xem Phòng
- Xem danh sách phòng có sẵn
- Xem chi tiết phòng: ảnh, mô tả, tiện nghi, giá, đánh giá
- Lọc theo loại phòng, giá, sức chứa

#### 4.2.2. Tạo Đặt Phòng
**Validation**:
- `check_in_date`: Phải >= hôm nay
- `check_out_date`: Phải > check_in_date
- `number_of_guests`: Phải <= capacity của phòng
- Kiểm tra trùng lịch với các booking khác

**Tính giá**:
```php
$nights = $checkIn->diffInDays($checkOut);
$totalPrice = $nights * $room->price_per_night;
```

**Trạng thái ban đầu**: `pending`

#### 4.2.3. Hủy Đặt Phòng
**Điều kiện**:
- Chỉ hủy được nếu status là `pending` hoặc `confirmed`
- Phải hủy trước **24 giờ** so với ngày check_in

**Xử lý hoàn tiền**:
- Nếu đã thanh toán (`payment_status = completed`):
  - Cập nhật `payment_status = refunded`
  - Ghi note về việc hoàn tiền
  - Thông báo thời gian hoàn tiền tùy theo phương thức

**Cập nhật**:
- `booking.status = cancelled`
- `room.status = available` (nếu đang occupied)

### 4.3. Thanh Toán (User)

#### 4.3.1. Phương thức thanh toán
Hiện tại chỉ hỗ trợ: **QR Chuyển khoản** (`bank_transfer_qr`)

#### 4.3.2. Quy trình thanh toán QR
1. User chọn thanh toán cho booking
2. Hệ thống tạo Payment với:
   - `payment_method = bank_transfer_qr`
   - `payment_status = pending`
   - `transaction_id = QR_{booking_id}_{timestamp}`
3. Hiển thị QR code với thông tin:
   - Số tài khoản ngân hàng
   - Tên chủ tài khoản
   - Số tiền
   - Nội dung chuyển khoản
4. User quét QR và chuyển khoản
5. User xác nhận đã chuyển khoản:
   - Upload ảnh biên lai (optional)
   - Nhập mã giao dịch (optional)
   - Nhập ghi chú (optional)
6. Payment vẫn ở trạng thái `pending` chờ Admin xác nhận

#### 4.3.3. Hủy Thanh Toán QR
**Điều kiện**:
- Chỉ hủy được nếu `payment_method = bank_transfer_qr`
- Chỉ hủy được nếu `payment_status = pending` hoặc `failed`
- Không hủy được nếu đã `completed` hoặc `refunded`

**Xử lý**:
- Nếu đã xác nhận chuyển khoản (có transaction_id hoặc receipt_image):
  - Yêu cầu nhập lý do hủy
- Cập nhật `payment_status = failed`
- Ghi note về việc hủy

### 4.4. Quản lý Thanh toán (Admin)

#### 4.4.1. Xác nhận Thanh toán
- Admin xem danh sách thanh toán chờ xử lý
- Xem chi tiết: Thông tin booking, ảnh biên lai, ghi chú
- Cập nhật trạng thái:
  - `completed`: Thanh toán thành công
    - Tự động cập nhật `booking.status = confirmed` (nếu booking đang pending)
    - Ghi `payment_date = now()`
  - `failed`: Thanh toán thất bại
  - `refunded`: Đã hoàn tiền

#### 4.4.2. Từ chối Thanh toán QR
- Chỉ từ chối được nếu `payment_method = bank_transfer_qr` và `status = pending`
- Yêu cầu nhập lý do từ chối
- Cập nhật:
  - `payment_status = failed`
  - `notes = "[ADMIN] Đã từ chối..."`
- Nếu booking đang `pending`:
  - `booking.status = cancelled`
  - `room.status = available`

**Lưu ý**: User không thể thanh toán lại nếu payment đã bị admin từ chối (có prefix `[ADMIN]` trong notes)

### 4.5. Quản lý Đặt Phòng (Admin)

#### 4.5.1. CRUD Đặt Phòng
- **Tạo**: Admin có thể tạo đặt phòng cho khách hàng
- **Sửa**: Cập nhật trạng thái, yêu cầu đặc biệt
- **Xóa**: Xóa đặt phòng
- **Xem**: Danh sách, chi tiết, lọc theo trạng thái

#### 4.5.2. Cập nhật Trạng thái
- `checked_in`: Khách đã nhận phòng
  - Tự động cập nhật `room.status = occupied`
- `checked_out`: Khách đã trả phòng
  - Tự động cập nhật `room.status = available`

#### 4.5.3. Export Đặt Phòng
- Chỉ Admin mới có quyền export
- Export ra file Excel với đầy đủ thông tin

### 4.6. Đánh giá (User & Admin)

#### 4.6.1. User đánh giá
- User có thể đánh giá phòng sau khi đã `checked_out`
- Nhập rating (1-5 sao) và comment
- Trạng thái ban đầu: `pending` (chờ admin duyệt)

#### 4.6.2. Admin duyệt đánh giá
- Xem danh sách đánh giá chờ duyệt
- **Approve**: Duyệt đánh giá → `status = approved`
- **Reject**: Từ chối đánh giá → `status = rejected`
- Chỉ đánh giá đã approved mới hiển thị công khai

### 4.7. Dashboard (Admin)

#### 4.7.1. Thống kê tổng quan
- Tổng số phòng, phòng có sẵn
- Tổng số khách hàng
- Tổng số đặt phòng, đặt phòng chờ xác nhận
- Doanh thu tổng, doanh thu tháng hiện tại
- Biểu đồ doanh thu 6 tháng gần đây
- Thống kê trạng thái đặt phòng

---

## 5. LUỒNG XỬ LÝ NGHIỆP VỤ

### 5.1. Luồng Đặt Phòng và Thanh Toán

```
1. User tìm kiếm và chọn phòng
   ↓
2. User điền thông tin đặt phòng (check_in, check_out, số người)
   ↓
3. Hệ thống kiểm tra:
   - Phòng có sẵn không?
   - Có trùng lịch không?
   - Số người <= capacity?
   ↓
4. Tạo Booking với status = 'pending'
   ↓
5. User chọn thanh toán
   ↓
6. Tạo Payment với:
   - payment_method = 'bank_transfer_qr'
   - payment_status = 'pending'
   ↓
7. Hiển thị QR code
   ↓
8. User chuyển khoản và xác nhận (upload biên lai)
   ↓
9. Admin xem và xác nhận thanh toán
   ↓
10. Nếu Admin xác nhận:
    - payment_status = 'completed'
    - booking.status = 'confirmed'
    - room.status = 'available' (giữ nguyên, chờ check_in)
```

### 5.2. Luồng Check-in/Check-out

```
1. Ngày check_in:
   - Admin cập nhật booking.status = 'checked_in'
   - room.status = 'occupied'
   ↓
2. Khách ở phòng
   ↓
3. Ngày check_out:
   - Admin cập nhật booking.status = 'checked_out'
   - room.status = 'available'
   ↓
4. Tự động cập nhật (nếu quá ngày check_out):
   - Hệ thống tự động set booking.status = 'checked_out'
   - room.status = 'available'
```

### 5.3. Luồng Hủy Đặt Phòng

```
1. User yêu cầu hủy đặt phòng
   ↓
2. Hệ thống kiểm tra:
   - Status có thể hủy? (pending/confirmed)
   - Còn >= 24h trước check_in?
   ↓
3. Nếu đã thanh toán:
   - payment_status = 'refunded'
   - Ghi note về hoàn tiền
   ↓
4. booking.status = 'cancelled'
   room.status = 'available'
```

### 5.4. Luồng Đánh giá

```
1. User đã checked_out
   ↓
2. User tạo đánh giá (rating, comment)
   - status = 'pending'
   ↓
3. Admin xem đánh giá
   ↓
4. Admin duyệt/từ chối:
   - Approve → status = 'approved' (hiển thị công khai)
   - Reject → status = 'rejected' (không hiển thị)
```

---

## 6. CẤU TRÚC CODE

### 6.1. Routes (`routes/web.php`)

#### User Routes
```php
// Public
GET  /                    → HomeController@index
GET  /rooms               → RoomController@index
GET  /rooms/{id}          → RoomController@show

// Auth required
POST /user/login          → UserAuthController@login
POST /user/register       → UserAuthController@register
GET  /dashboard           → DashboardController@index
GET  /profile             → ProfileController@index
PUT  /profile/update       → ProfileController@update

// Bookings
GET  /bookings            → BookingController@index
GET  /bookings/create     → BookingController@create
POST /bookings            → BookingController@store
GET  /bookings/{id}       → BookingController@show
POST /bookings/{id}/cancel → BookingController@cancel

// Payments
GET  /payments/booking/{bookingId} → PaymentController@create
POST /payments            → PaymentController@store
GET  /payments/qr/{id}    → PaymentController@showQR
POST /payments/confirm/{id} → PaymentController@confirmPayment
POST /payments/cancel/{id} → PaymentController@cancelPayment

// Reviews
GET  /reviews             → ReviewController@index
GET  /reviews/create      → ReviewController@create
POST /reviews             → ReviewController@store
```

#### Admin Routes
```php
// Auth
POST /admin/login         → AdminAuthController@login

// Dashboard
GET  /admin/dashboard     → DashboardController@index

// Rooms
GET  /admin/rooms         → AdminRoomController@index
POST /admin/rooms         → AdminRoomController@store
GET  /admin/rooms/scrape  → AdminRoomController@showScrapeForm
POST /admin/rooms/scrape  → AdminRoomController@scrape
GET  /admin/rooms/import  → AdminRoomController@showImportForm
POST /admin/rooms/import  → AdminRoomController@import

// Bookings
GET  /admin/bookings      → AdminBookingController@index
GET  /admin/bookings/export → AdminBookingController@export (Admin only)

// Payments
GET  /admin/payments      → AdminPaymentController@index
PUT  /admin/payments/{id}  → AdminPaymentController@update
POST /admin/payments/{id}/reject → AdminPaymentController@rejectPayment

// Staff (Admin only)
GET  /admin/staff         → StaffController@index
```

### 6.2. Controllers

#### User Controllers

**BookingController**:
- `index()`: Danh sách đặt phòng của user, tự động cập nhật checked_out
- `create()`: Form tạo đặt phòng
- `store()`: Xử lý tạo đặt phòng, kiểm tra trùng lịch
- `show()`: Chi tiết đặt phòng
- `cancel()`: Hủy đặt phòng, xử lý hoàn tiền

**PaymentController**:
- `create()`: Form thanh toán
- `store()`: Tạo payment QR
- `showQR()`: Hiển thị QR code
- `confirmPayment()`: User xác nhận đã chuyển khoản
- `cancelPayment()`: Hủy thanh toán QR

#### Admin Controllers

**RoomController**:
- CRUD phòng
- `scrape()`: Scrape phòng từ website
- `scrapeAndExport()`: Scrape và export Excel
- `import()`: Import từ Excel
- `deleteImage()`: Xóa ảnh phòng
- `setPrimaryImage()`: Đặt ảnh chính

**PaymentController**:
- `index()`: Danh sách thanh toán, lọc theo status/method
- `update()`: Cập nhật trạng thái thanh toán, tự động cập nhật booking
- `rejectPayment()`: Từ chối thanh toán QR

**BookingController**:
- CRUD đặt phòng
- `export()`: Export Excel (Admin only)
- Tự động cập nhật room status khi thay đổi booking status

**DashboardController**:
- `index()`: Thống kê tổng quan, biểu đồ doanh thu

### 6.3. Services

**VNPayService** (`app/Services/VNPayService.php`):
- `createPaymentUrl()`: Tạo URL thanh toán VNPay
- `validateCallback()`: Xác thực callback từ VNPay

---

## 7. MODELS VÀ RELATIONSHIPS

### 7.1. User Model
```php
// Relationships
hasMany(Booking::class)
hasMany(Review::class)

// Methods
- bookings() → Collection<Booking>
- reviews() → Collection<Review>
```

### 7.2. Room Model
```php
// Relationships
hasMany(Booking::class)
hasMany(RoomImage::class)
hasMany(Review::class)
hasOne(RoomImage::class)->where('is_primary', true) // primaryImage

// Scopes
available() → QueryBuilder
byType($type) → QueryBuilder

// Methods
- bookings() → Collection<Booking>
- images() → Collection<RoomImage> (ordered by order, id)
- primaryImage() → RoomImage|null
- reviews() → Collection<Review>
- approvedReviews() → Collection<Review> (status = approved)
```

### 7.3. Booking Model
```php
// Relationships
belongsTo(User::class)
belongsTo(Room::class)
hasOne(Payment::class)
hasOne(Review::class)

// Scopes
pending() → QueryBuilder
confirmed() → QueryBuilder
active() → QueryBuilder (confirmed, checked_in)
overlapping($roomId, $checkIn, $checkOut, $excludeId) → QueryBuilder

// Methods
- user() → User
- room() → Room
- payment() → Payment|null
- review() → Review|null
```

**Logic overlapping**:
```php
// Hai booking overlap nếu:
check_in_date mới < check_out_date cũ 
AND 
check_out_date mới > check_in_date cũ

// Chỉ kiểm tra với status: pending, confirmed, checked_in
```

### 7.4. Payment Model
```php
// Relationships
belongsTo(Booking::class)

// Scopes
completed() → QueryBuilder
pending() → QueryBuilder

// Methods
- booking() → Booking
```

### 7.5. Review Model
```php
// Relationships
belongsTo(User::class)
belongsTo(Room::class)
belongsTo(Booking::class)

// Scopes
approved() → QueryBuilder
pending() → QueryBuilder
byRoom($roomId) → QueryBuilder

// Methods
- user() → User
- room() → Room
- booking() → Booking
```

---

## 8. CONTROLLERS VÀ LOGIC

### 8.1. BookingController (User)

#### `store()` - Tạo đặt phòng
```php
1. Validate input:
   - room_id: required, exists
   - check_in_date: required, date, >= today
   - check_out_date: required, date, > check_in_date
   - number_of_guests: required, integer, min:1

2. Tính giá:
   $nights = diffInDays(check_in, check_out)
   $totalPrice = $nights * $room->price_per_night

3. Kiểm tra trùng lịch:
   Booking::overlapping(room_id, check_in, check_out)
   → Nếu có → Error

4. Kiểm tra capacity:
   number_of_guests <= room->capacity
   → Nếu không → Error

5. Tạo booking:
   status = 'pending'
```

#### `cancel()` - Hủy đặt phòng
```php
1. Kiểm tra quyền: user_id === Auth::id()

2. Kiểm tra status:
   in_array(status, ['pending', 'confirmed'])
   → Nếu không → Error

3. Kiểm tra thời gian:
   hoursUntilCheckIn = diffInHours(check_in, now)
   → Nếu < 24h → Error

4. Xử lý hoàn tiền (nếu đã thanh toán):
   if (payment && payment_status === 'completed'):
     payment_status = 'refunded'
     notes += "Đã hoàn tiền..."

5. Cập nhật:
   booking.status = 'cancelled'
   room.status = 'available'
```

### 8.2. PaymentController (User)

#### `store()` - Tạo thanh toán QR
```php
1. Validate:
   - booking_id: required, exists
   - payment_method: required, in:bank_transfer_qr

2. Kiểm tra booking:
   - status !== 'cancelled'
   - payment không bị admin từ chối

3. Tạo/Update payment:
   Payment::updateOrCreate(
     ['booking_id' => $booking->id],
     [
       'amount' => $booking->total_price,
       'payment_method' => 'bank_transfer_qr',
       'payment_status' => 'pending',
       'transaction_id' => 'QR_' . $booking->id . '_' . time()
     ]
   )

4. Redirect đến trang QR
```

#### `confirmPayment()` - Xác nhận đã chuyển khoản
```php
1. Validate:
   - transaction_id: optional
   - receipt_image: optional, image, max:2MB
   - notes: optional

2. Upload ảnh biên lai (nếu có)

3. Cập nhật payment:
   - transaction_id (nếu có)
   - receipt_image (nếu có)
   - notes (nếu có)
   - payment_status vẫn là 'pending' (chờ admin xác nhận)
```

### 8.3. PaymentController (Admin)

#### `update()` - Cập nhật trạng thái thanh toán
```php
1. Validate:
   - payment_status: required, in:pending,completed,failed,refunded
   - transaction_id: optional
   - notes: optional

2. Nếu payment_status = 'completed':
   - payment_date = now() (nếu chưa có)
   - Nếu booking.status = 'pending':
     booking.status = 'confirmed'

3. Nếu payment_status = 'refunded':
   - Nếu booking.status in ['pending', 'confirmed']:
     booking.status = 'cancelled'

4. Cập nhật payment
```

#### `rejectPayment()` - Từ chối thanh toán QR
```php
1. Kiểm tra:
   - payment_method === 'bank_transfer_qr'
   - payment_status === 'pending'
   → Nếu không → Error

2. Validate:
   - reject_reason: required

3. Cập nhật payment:
   payment_status = 'failed'
   notes = "[ADMIN] Đã từ chối... Lý do: {reason}"

4. Cập nhật booking:
   if (booking.status === 'pending'):
     booking.status = 'cancelled'
     room.status = 'available'
```

### 8.4. RoomController (Admin)

#### `scrape()` - Scrape phòng từ website
```php
1. Validate URL

2. Detect website type:
   - booking.com → 'booking'
   - agoda.com → 'agoda'
   - expedia.com → 'expedia'
   - etc.

3. Fetch HTML với headers phù hợp:
   Http::withHeaders($headers)->get($url)

4. Parse HTML/JSON:
   - Tìm JSON-LD structured data
   - Tìm JSON trong script tags
   - Parse HTML structure

5. Extract room data:
   - room_number, room_type, capacity
   - price_per_night, description
   - amenities, images

6. Download images về server

7. Lưu vào database:
   - Tạo Room
   - Tạo RoomImage cho mỗi ảnh
   - Set ảnh đầu tiên làm primary
```

---

## 9. AUTHENTICATION & AUTHORIZATION

### 9.1. Multi-Guard Setup

**Config** (`config/auth.php`):
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
],
```

### 9.2. Admin Roles

**Admin Model** (`app/Models/Admin.php`):
```php
// Methods
isAdmin() → bool
isManager() → bool
canAccess($permission) → bool

// Permissions
Admin: Toàn quyền
Manager: 
  - view_dashboard
  - manage_rooms
  - manage_bookings
  - manage_payments
  - manage_customers
  - manage_reviews
```

### 9.3. Middleware

**Role Middleware** (`app/Http/Middleware/RoleAdmin.php`):
- Chỉ Admin mới có quyền truy cập
- Manager bị chặn

**Usage**:
```php
Route::middleware('role.admin:admin')->group(function () {
    // Chỉ Admin
});
```

---

## 10. TÍNH NĂNG ĐẶC BIỆT

### 10.1. Tự động cập nhật trạng thái

**BookingController@index**:
- Tự động set `booking.status = 'checked_out'` nếu quá ngày check_out
- Tự động set `room.status = 'available'` nếu booking đã checked_out

**PaymentController@update** (Admin):
- Tự động cập nhật `booking.status` khi `payment_status` thay đổi

### 10.2. Kiểm tra trùng lịch

**Booking Model - Scope `overlapping()`**:
```php
// Logic: Hai khoảng thời gian overlap nếu:
check_in_date mới < check_out_date cũ 
AND 
check_out_date mới > check_in_date cũ

// Chỉ kiểm tra với status: pending, confirmed, checked_in
```

### 10.3. QR Code Chuyển khoản

#### 10.3.1. Thư viện sử dụng
- **Package**: `simplesoftwareio/simple-qrcode` (v4.2+)
- **Facade**: `QrCode`

#### 10.3.2. Quy trình tạo QR Code

**PaymentController@showQR**:

1. **Lấy thông tin từ Environment Variables**:
```php
$bankAccount = env('BANK_ACCOUNT', '1234567890');
$bankName = env('BANK_NAME', 'Ngân hàng ABC');
$accountName = env('BANK_ACCOUNT_NAME', 'CÔNG TY TNHH KHÁCH SẠN');
$bankBin = env('BANK_BIN', ''); // Mã BIN ngân hàng (tùy chọn)
```

2. **Tạo nội dung chuyển khoản**:
```php
$transferContent = "THANHTOAN " . $payment->booking_id . " " . $payment->transaction_id;
// Ví dụ: "THANHTOAN 123 QR_123_1234567890"
```

3. **Loại bỏ dấu tiếng Việt** (để tránh lỗi encoding):
```php
$accountNameNoAccent = $this->removeVietnameseAccent($accountName);
// "CÔNG TY TNHH KHÁCH SẠN" → "CONG TY TNHH KHACH SAN"
```

4. **Tạo chuỗi QR Data**:
```php
// Format: STK|TEN_CHU_TK|SO_TIEN|NOI_DUNG
$qrData = "{$bankAccount}|{$accountNameNoAccent}|{$payment->amount}|{$transferContent}";
// Ví dụ: "1234567890|CONG TY TNHH KHACH SAN|500000|THANHTOAN 123 QR_123_1234567890"
```

5. **Generate QR Code trong View**:
```blade
@php
    // Đảm bảo QR data chỉ chứa ASCII
    $qrDataSafe = mb_convert_encoding($qrData, 'ASCII', 'UTF-8');
    $qrDataSafe = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $qrData);
@endphp
{!! QrCode::size(300)->errorCorrection('H')->encoding('UTF-8')->generate($qrDataSafe) !!}
```

#### 10.3.3. Xử lý Encoding

**Vấn đề**: 
- QR code cần dữ liệu ASCII để tương thích với các app ngân hàng
- Tên tiếng Việt có dấu gây lỗi encoding

**Giải pháp** - Method `removeVietnameseAccent()`:
```php
1. Chuyển đổi sang UTF-8 nếu chưa phải
2. Loại bỏ dấu tiếng Việt bằng regex:
   - à,á,ạ,ả,ã,â,ầ,ấ,ậ,ẩ,ẫ,ă,ằ,ắ,ặ,ẳ,ẵ → a
   - è,é,ẹ,ẻ,ẽ,ê,ề,ế,ệ,ể,ễ → e
   - ì,í,ị,ỉ,ĩ → i
   - ò,ó,ọ,ỏ,õ,ô,ồ,ố,ộ,ổ,ỗ,ơ,ờ,ớ,ợ,ở,ỡ → o
   - ù,ú,ụ,ủ,ũ,ư,ừ,ứ,ự,ử,ữ → u
   - ỳ,ý,ỵ,ỷ,ỹ → y
   - đ → d
3. Sử dụng iconv với TRANSLIT để chuyển đổi ký tự đặc biệt
4. Loại bỏ các ký tự không phải ASCII còn sót lại
```

#### 10.3.4. Format QR Code

**Format hiện tại**: `STK|TEN_CHU_TK|SO_TIEN|NOI_DUNG`

**Ví dụ**:
```
1234567890|CONG TY TNHH KHACH SAN|500000|THANHTOAN 123 QR_123_1234567890
```

**Lưu ý**:
- Format này tương thích với nhiều app ngân hàng Việt Nam
- Có thể mở rộng sang VietQR format nếu có BIN ngân hàng (đã comment trong code)

#### 10.3.5. Cấu hình QR Code

**Trong View** (`resources/views/user/payments/qr.blade.php`):
```blade
QrCode::size(300)                    // Kích thước 300x300px
      ->errorCorrection('H')         // Mức sửa lỗi cao (High)
      ->encoding('UTF-8')            // Encoding UTF-8
      ->generate($qrDataSafe)        // Generate QR từ data
```

**Error Correction Levels**:
- `L` (Low): ~7% lỗi có thể sửa
- `M` (Medium): ~15% lỗi có thể sửa
- `Q` (Quartile): ~25% lỗi có thể sửa
- `H` (High): ~30% lỗi có thể sửa (đang dùng)

#### 10.3.6. Hiển thị QR Code

**Thông tin hiển thị**:
1. **QR Code**: Mã QR để quét
2. **Thông tin chuyển khoản**:
   - Số tài khoản (có nút Copy)
   - Ngân hàng
   - Chủ tài khoản
   - Số tiền (có nút Copy)
   - Nội dung chuyển khoản (có nút Copy)
   - Mã đặt phòng
3. **Thông tin đặt phòng**: Phòng, ngày nhận/trả, tổng tiền
4. **Hướng dẫn**: 5 bước thanh toán
5. **Form xác nhận** (nếu status = pending):
   - Mã giao dịch (optional)
   - Upload ảnh biên lai (optional)
   - Ghi chú (optional)

#### 10.3.7. Xác nhận Thanh toán QR

**PaymentController@confirmPayment**:

1. **Validation**:
   - `transaction_id`: optional, string, max:255
   - `receipt_image`: optional, image, mimes:jpeg,png,jpg,gif, max:2MB
   - `notes`: optional, string, max:1000

2. **Xử lý**:
   - Upload ảnh biên lai vào `storage/app/public/receipts/`
   - Cập nhật `transaction_id`, `receipt_image`, `notes`
   - **Giữ `payment_status = 'pending'`** (chờ admin xác nhận)

3. **Lưu ý**: 
   - Không tự động set `completed` để admin có thể kiểm tra
   - Có thể bật tự động xác nhận bằng cách uncomment code trong controller

#### 10.3.8. Hủy Thanh toán QR

**PaymentController@cancelPayment**:

**Điều kiện**:
- Chỉ hủy được nếu `payment_method = bank_transfer_qr`
- Chỉ hủy được nếu `payment_status = pending` hoặc `failed`
- Không hủy được nếu đã `completed` hoặc `refunded`

**Xử lý đặc biệt**:
- Nếu đã xác nhận chuyển khoản (có `transaction_id` hoặc `receipt_image`):
  - **Yêu cầu bắt buộc** nhập lý do hủy
- Cập nhật `payment_status = failed`
- Ghi note về việc hủy

#### 10.3.9. Environment Variables cần cấu hình

Thêm vào file `.env`:
```env
BANK_ACCOUNT=1234567890
BANK_NAME=Ngân hàng ABC
BANK_ACCOUNT_NAME=CÔNG TY TNHH KHÁCH SẠN
BANK_BIN=          # Tùy chọn, mã BIN ngân hàng
```

#### 10.3.10. Tương thích App Ngân hàng

**Format hiện tại tương thích với**:
- Vietcombank
- BIDV
- Techcombank
- Agribank
- MBBank
- Và nhiều app ngân hàng khác

**Cách hoạt động**:
1. User quét QR code bằng app ngân hàng
2. App tự động điền: Số tài khoản, Tên chủ TK, Số tiền, Nội dung
3. User xác nhận và chuyển khoản
4. User xác nhận trong hệ thống (upload biên lai)
5. Admin kiểm tra và xác nhận thanh toán

### 10.4. Scrape Phòng

**Hỗ trợ nhiều website**:
- Booking.com, Agoda, Expedia, Hotels.com
- Traveloka, VnTravel, Mytour.vn, Luxstay

**Phương pháp trích xuất**:
1. JSON-LD structured data
2. JSON trong script tags
3. HTML structure parsing

**Xử lý lỗi**:
- Retry với delay
- Timeout 30s
- Log lỗi chi tiết
- Thông báo lỗi cụ thể cho user

### 10.5. Export/Import Excel

**Export**:
- Bookings: `BookingsExport`
- Rooms Template: `RoomsTemplateExport`
- Scraped Rooms: `ScrapedRoomsExport`

**Import**:
- Rooms: `RoomsImport`
- Validation và xử lý lỗi

---

## KẾT LUẬN

Hệ thống quản lý khách sạn này được xây dựng với:
- **Kiến trúc rõ ràng**: MVC, Multi-Guard Auth
- **Nghiệp vụ đầy đủ**: Đặt phòng, thanh toán, đánh giá
- **Tính năng nâng cao**: Scrape phòng, QR thanh toán, Export/Import
- **Bảo mật**: Phân quyền Admin/Manager, Validation đầy đủ
- **Tự động hóa**: Tự động cập nhật trạng thái, kiểm tra trùng lịch

Tài liệu này cung cấp cái nhìn tổng quan về code và nghiệp vụ, giúp developer mới có thể hiểu và phát triển hệ thống một cách hiệu quả.

