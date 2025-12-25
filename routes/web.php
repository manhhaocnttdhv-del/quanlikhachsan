<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\RoomController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ShiftReportController;
use App\Http\Controllers\Employee\CheckoutController;
use App\Http\Controllers\Employee\ReportController;
use App\Http\Controllers\User\ReviewController as UserReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;

/*
|--------------------------------------------------------------------------
| Web Routes - User (Người dùng)
|--------------------------------------------------------------------------
*/

// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/agoda-clone', [HomeController::class, 'agodaClone'])->name('agoda-clone');

// Authentication - Đăng ký / Đăng nhập
Route::prefix('user')->name('user.')->group(function () {
    Route::get('login', [UserAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [UserAuthController::class, 'login']);
    Route::get('register', [UserAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
    
    // Password Reset
    Route::get('forgot-password', [\App\Http\Controllers\User\PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\User\PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\User\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\User\PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Tìm kiếm & xem phòng
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('index');
    Route::get('/{id}', [RoomController::class, 'show'])->name('show');
});

// Dashboard (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('dashboard')->name('user.dashboard.')->group(function () {
    Route::get('/', [\App\Http\Controllers\User\DashboardController::class, 'index'])->name('index');
});

// Quản lý thông tin cá nhân (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('profile')->name('user.profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

// Đặt phòng & Quản lý đặt phòng (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('bookings')->name('user.bookings.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/create', [BookingController::class, 'create'])->name('create');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::get('/{id}', [BookingController::class, 'show'])->name('show');
    Route::post('/{id}/cancel', [BookingController::class, 'cancel'])->name('cancel');
});

// Thanh toán (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('payments')->name('user.payments.')->group(function () {
    Route::get('/booking/{bookingId}', [PaymentController::class, 'create'])->name('create');
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::get('/qr/{paymentId}', [PaymentController::class, 'showQR'])->name('qr');
    Route::post('/confirm/{paymentId}', [PaymentController::class, 'confirmPayment'])->name('confirm');
    Route::post('/cancel/{paymentId}', [PaymentController::class, 'cancelPayment'])->name('cancel');
    Route::get('/{paymentId}', [PaymentController::class, 'show'])->name('show');
});

// Hoàn tiền (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('refunds')->name('user.refunds.')->group(function () {
    Route::get('/payment/{paymentId}', [\App\Http\Controllers\User\RefundController::class, 'create'])->name('create');
    Route::post('/payment/{paymentId}', [\App\Http\Controllers\User\RefundController::class, 'store'])->name('store');
});

// Đánh giá (yêu cầu đăng nhập)
Route::middleware('auth')->prefix('reviews')->name('user.reviews.')->group(function () {
    Route::get('/', [UserReviewController::class, 'index'])->name('index');
    Route::get('/create', [UserReviewController::class, 'create'])->name('create');
    Route::post('/', [UserReviewController::class, 'store'])->name('store');
});

/*
|--------------------------------------------------------------------------
| Web Routes - Admin
|--------------------------------------------------------------------------
*/

// Admin Authentication
Route::prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin to dashboard if authenticated, otherwise to login
    Route::get('/', function () {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });

    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Admin Routes (yêu cầu đăng nhập admin)
    Route::middleware('auth:admin')->group(function () {
        // Dashboard - Cả admin và manager đều có quyền
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
        Route::get('dashboard/export/available-rooms', [DashboardController::class, 'exportAvailableRooms'])->name('dashboard.export.available-rooms');
        Route::get('dashboard/export/occupied-rooms', [DashboardController::class, 'exportOccupiedRooms'])->name('dashboard.export.occupied-rooms');
        Route::get('dashboard/export/paid-rooms', [DashboardController::class, 'exportPaidRooms'])->name('dashboard.export.paid-rooms');
        Route::get('dashboard/export/recent-bookings', [DashboardController::class, 'exportRecentBookings'])->name('dashboard.export.recent-bookings');

        // Quản lý phòng - Cả admin và manager đều có quyền
        Route::get('rooms/scrape', [AdminRoomController::class, 'showScrapeForm'])->name('rooms.scrape.form');
        Route::post('rooms/scrape', [AdminRoomController::class, 'scrape'])->name('rooms.scrape');
        Route::post('rooms/scrape/export', [AdminRoomController::class, 'scrapeAndExport'])->name('rooms.scrape.export');
        Route::get('rooms/import', [AdminRoomController::class, 'showImportForm'])->name('rooms.import.form');
        Route::post('rooms/import', [AdminRoomController::class, 'import'])->name('rooms.import');
        Route::get('rooms/import/template', [AdminRoomController::class, 'downloadTemplate'])->name('rooms.import.template');
        Route::delete('rooms/images/{id}', [AdminRoomController::class, 'deleteImage'])->name('rooms.images.delete');
        Route::post('rooms/images/{id}/primary', [AdminRoomController::class, 'setPrimaryImage'])->name('rooms.images.primary');
        Route::resource('rooms', AdminRoomController::class);

        // Quản lý khách hàng - Cả admin và manager đều có quyền
        Route::resource('customers', CustomerController::class)->except(['create', 'store']);

        // Export bookings - Phải đặt trước resource route để tránh conflict
        Route::get('bookings/export', [AdminBookingController::class, 'export'])->name('bookings.export');
        
        // Quản lý đặt phòng - Cả admin và manager đều có quyền
        Route::resource('bookings', AdminBookingController::class);

        // Quản lý hóa đơn / thanh toán - Cả admin và manager đều có quyền
        Route::resource('payments', AdminPaymentController::class);
        Route::post('payments/{id}/reject', [AdminPaymentController::class, 'rejectPayment'])->name('payments.reject');

        // Quản lý yêu cầu hoàn tiền - Cả admin và manager đều có quyền
        Route::prefix('refunds')->name('refunds.')->group(function () {
            Route::get('/', [AdminRefundController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminRefundController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [AdminRefundController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [AdminRefundController::class, 'reject'])->name('reject');
            Route::post('/{id}/complete', [AdminRefundController::class, 'complete'])->name('complete');
        });

        // Quản lý đánh giá - Cả admin và manager đều có quyền
        Route::resource('reviews', AdminReviewController::class)->except(['create', 'store', 'edit', 'update']);
        Route::post('reviews/{id}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{id}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');

        // Quản lý nhân viên - CHỈ ADMIN mới có quyền
        Route::middleware('role.admin:admin')->group(function () {
            Route::resource('staff', StaffController::class);
            // Quản lý ca làm việc
            Route::resource('shifts', ShiftController::class);
            Route::get('shifts/monthly/create', [ShiftController::class, 'createMonthly'])->name('shifts.createMonthly');
            Route::post('shifts/monthly', [ShiftController::class, 'storeMonthly'])->name('shifts.storeMonthly');
        });

        // Nhân viên - Xem checkout và đặt phòng (employee và manager)
        Route::prefix('employee')->name('employee.')->group(function () {
            // Checkout
            Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
            Route::get('checkout/{id}', [CheckoutController::class, 'show'])->name('checkout.show');
            Route::post('checkout/{id}/checkin', [CheckoutController::class, 'checkIn'])->name('checkout.checkin');
            Route::post('checkout/{id}/checkout', [CheckoutController::class, 'checkout'])->name('checkout.process');
            
            // Đặt phòng cho khách
            Route::get('bookings', [\App\Http\Controllers\Employee\BookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/create', [\App\Http\Controllers\Employee\BookingController::class, 'create'])->name('bookings.create');
            Route::post('bookings', [\App\Http\Controllers\Employee\BookingController::class, 'store'])->name('bookings.store');
            Route::get('bookings/available-rooms', [\App\Http\Controllers\Employee\BookingController::class, 'getAvailableRooms'])->name('bookings.availableRooms');
            Route::post('bookings/create-customer', [\App\Http\Controllers\Employee\BookingController::class, 'createCustomer'])->name('bookings.createCustomer');
            
            // Thanh toán
            Route::get('payments/{bookingId}/create', [\App\Http\Controllers\Employee\PaymentController::class, 'create'])->name('payments.create');
            Route::post('payments/{bookingId}', [\App\Http\Controllers\Employee\PaymentController::class, 'store'])->name('payments.store');
            Route::put('payments/{bookingId}', [\App\Http\Controllers\Employee\PaymentController::class, 'update'])->name('payments.update');
            
            // Quản lý khách hàng
            Route::get('customers', [\App\Http\Controllers\Employee\CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{id}', [\App\Http\Controllers\Employee\CustomerController::class, 'show'])->name('customers.show');
            Route::get('customers/{id}/edit', [\App\Http\Controllers\Employee\CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{id}', [\App\Http\Controllers\Employee\CustomerController::class, 'update'])->name('customers.update');
            
            // Xem phòng trống
            Route::get('rooms/availability', [\App\Http\Controllers\Employee\RoomAvailabilityController::class, 'index'])->name('rooms.availability');
            Route::get('rooms/search', [\App\Http\Controllers\Employee\RoomAvailabilityController::class, 'search'])->name('rooms.search');
            Route::get('rooms/calendar', [\App\Http\Controllers\Employee\RoomAvailabilityController::class, 'calendar'])->name('rooms.calendar');
            
            // Hóa đơn
            Route::get('invoices/{bookingId}', [\App\Http\Controllers\Employee\InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('invoices/{bookingId}/print', [\App\Http\Controllers\Employee\InvoiceController::class, 'print'])->name('invoices.print');
            
            // Tìm kiếm nhanh
            Route::get('quick-search', [\App\Http\Controllers\Employee\QuickSearchController::class, 'search'])->name('quick-search');
            
            // Gửi email thông báo
            Route::post('notifications/{bookingId}/send-email', [\App\Http\Controllers\Employee\NotificationController::class, 'sendEmail'])->name('notifications.send-email');
            Route::post('notifications/send-bulk', [\App\Http\Controllers\Employee\NotificationController::class, 'sendBulk'])->name('notifications.send-bulk');
            
            // Báo cáo ca
            Route::resource('reports', ReportController::class);
            
            // Quản lý ca làm việc của nhân viên
            Route::get('shifts', [\App\Http\Controllers\Employee\ShiftController::class, 'index'])->name('shifts.index');
            Route::get('shifts/{id}', [\App\Http\Controllers\Employee\ShiftController::class, 'show'])->name('shifts.show');
            Route::post('shifts/{id}/update-status', [\App\Http\Controllers\Employee\ShiftController::class, 'updateStatus'])->name('shifts.updateStatus');
            Route::delete('shifts/{id}', [\App\Http\Controllers\Employee\ShiftController::class, 'destroy'])->name('shifts.destroy');
        });

        // Admin - Xem doanh thu theo ca
        Route::middleware('role.admin:admin')->group(function () {
            Route::prefix('shift-reports')->name('shift-reports.')->group(function () {
                Route::get('/', [ShiftReportController::class, 'index'])->name('index');
                Route::get('by-employee', [ShiftReportController::class, 'byEmployee'])->name('by-employee');
                Route::get('by-date', [ShiftReportController::class, 'byDate'])->name('by-date');
                Route::get('{id}', [ShiftReportController::class, 'show'])->name('show');
            });
        });
    });
});
