@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<style>
    .period-btn {
        min-width: 100px;
        transition: all 0.3s ease;
    }
    .period-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .period-btn.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
</style>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    @if(!auth('admin')->user()->isEmployee())
    <a href="{{ route('admin.dashboard.export', request()->all()) }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Xuất Excel
    </a>
    @endif
</div>

@if(auth('admin')->user()->isEmployee() && isset($currentShift))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-clock fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">
                    <i class="fas fa-check-circle"></i> Bạn đang làm ca: {{ $currentShift->getShiftTypeName() }}
                </h5>
                <p class="mb-0">
                    <strong>Thời gian:</strong> {{ $currentShift->start_time }} - {{ $currentShift->end_time }} | 
                    <strong>Ngày:</strong> {{ \Carbon\Carbon::parse($currentShift->shift_date)->format('d/m/Y') }}
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif(auth('admin')->user()->isEmployee() && (!isset($currentShift) || !$currentShift))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">
                    <i class="fas fa-clock"></i> Chưa có ca làm việc active
                </h5>
                <p class="mb-0">
                    Bạn chưa có ca làm việc active. Vui lòng đăng nhập lại hoặc liên hệ quản lý để được phân công ca.
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(auth('admin')->user()->isEmployee() && isset($notifications))
    <!-- Thông báo nhắc nhở cho nhân viên -->
    @if($notifications['upcoming_check_outs']->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Booking sắp check-out hôm nay ({{ $notifications['upcoming_check_outs']->count() }})</h5>
                <ul class="mb-0">
                    @foreach($notifications['upcoming_check_outs']->take(5) as $booking)
                        <li>
                            <strong>#{{ $booking->id }}</strong> - {{ $booking->user->name }} - 
                            Phòng {{ $booking->room->room_number }} - 
                            {{ \Carbon\Carbon::parse($booking->check_out_time)->format('H:i') }}
                            @if(!$booking->payment || $booking->payment->payment_status != 'completed')
                                <span class="badge bg-danger ms-2">Chưa thanh toán</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if($notifications['upcoming_check_outs']->count() > 5)
                    <small class="text-muted">... và {{ $notifications['upcoming_check_outs']->count() - 5 }} booking khác</small>
                @endif
            </div>
            <form action="{{ route('admin.employee.notifications.send-bulk') }}" method="POST" class="ms-2">
                @csrf
                <input type="hidden" name="type" value="check_out_reminder">
                <button type="submit" class="btn btn-sm btn-light" onclick="return confirm('Gửi email cho tất cả booking check-out hôm nay?');">
                    <i class="fas fa-envelope"></i> Gửi email
                </button>
            </form>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
@endif

@if(!auth('admin')->user()->isEmployee())
<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Lọc theo thời gian</h5>
    </div>
    <div class="card-body">
        @if($errors->has('date_to'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first('date_to') }}
            </div>
        @endif
        <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" id="dateFrom" class="form-control" value="{{ request('date_from', $dateFrom ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" id="dateTo" class="form-control" value="{{ request('date_to', $dateTo ?? '') }}" required>
                <div class="invalid-feedback" id="dateToError" style="display: none;">
                    Đến ngày không được nhỏ hơn từ ngày
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>
@endif

@if(!auth('admin')->user()->isEmployee())
@if($dateFrom && $dateTo)
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle"></i> 
    <strong>Khoảng thời gian đã chọn:</strong>
    Từ {{ \Carbon\Carbon::parse($dateFrom)->format(format: 'd/m/Y') }} đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    | <strong>Doanh thu:</strong> {{ number_format($revenueStats['filtered']) }} VNĐ
</div>
@endif
@endif

@if(!auth('admin')->user()->isEmployee())
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="#available-rooms-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-primary text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Tổng phòng</h6>
                            <h2 class="mb-0">{{ $stats['total_rooms'] }}</h2>
                        </div>
                        <i class="fas fa-bed fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 mb-3">
        <a href="#available-rooms-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-success text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Phòng trống</h6>
                            <h2 class="mb-0">{{ $stats['available_rooms'] }}</h2>
                        </div>
                        <i class="fas fa-door-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 mb-3">
        <a href="#recent-bookings-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-info text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Khách hàng</h6>
                            <h2 class="mb-0">{{ $stats['total_customers'] }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 mb-3">
        <a href="#recent-bookings-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-warning text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Đặt phòng</h6>
                            <h2 class="mb-0">{{ $stats['total_bookings'] }}</h2>
                        </div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endif

@if(!auth('admin')->user()->isEmployee())
<!-- Revenue Cards -->
<div class="row mb-4">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Doanh thu tổng (Từ tất cả thanh toán)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Hôm nay</h6>
                            <h4 class="text-primary mb-0">{{ number_format($revenueStats['today']) }} ₫</h4>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Tuần này</h6>
                            <h4 class="text-info mb-0">{{ number_format($revenueStats['this_week']) }} ₫</h4>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Tháng này</h6>
                            <h4 class="text-success mb-0">{{ number_format($revenueStats['this_month']) }} ₫</h4>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Năm này</h6>
                            <h4 class="text-warning mb-0">{{ number_format($revenueStats['this_year']) }} ₫</h4>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Tổng cộng</h6>
                            <h4 class="text-danger mb-0">{{ number_format($revenueStats['total']) }} ₫</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(!auth('admin')->user()->isEmployee())
<!-- Room Status Today -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <a href="#available-rooms-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-success text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Phòng trống{{ $dateFrom && $dateTo ? ' (' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') . ')' : ' hôm nay' }}</h6>
                            <h2 class="mb-0">{{ $stats['available_rooms_today'] }}</h2>
                            <small class="opacity-75">Trong tổng số {{ $stats['total_rooms'] }} phòng</small>
                        </div>
                        <i class="fas fa-door-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="#occupied-rooms-section" class="text-decoration-none scroll-to-section">
            <div class="card bg-info text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Phòng có khách{{ $dateFrom && $dateTo ? ' (' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') . ')' : ' hôm nay' }}</h6>
                            <h2 class="mb-0">{{ $stats['occupied_rooms_today'] }}</h2>
                            <small class="opacity-75">{{ $stats['checked_in_today'] }} phòng đã check-in</small>
                        </div>
                        <i class="fas fa-bed fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="{{ route('admin.rooms.index', ['status' => 'maintenance']) }}" class="text-decoration-none">
            <div class="card bg-warning text-white" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Phòng bảo trì</h6>
                            <h2 class="mb-0">{{ $stats['maintenance_rooms'] }}</h2>
                            <small class="opacity-75">Không thể sử dụng</small>
                        </div>
                        <i class="fas fa-tools fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Booking Status -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <a href="#recent-bookings-section" class="text-decoration-none scroll-to-section">
            <div class="card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body text-center">
                    <h6 class="text-muted">Đặt phòng chờ xử lý</h6>
                    <h3 class="text-warning">{{ $stats['pending_bookings'] }}</h3>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="#recent-bookings-section" class="text-decoration-none scroll-to-section">
            <div class="card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body text-center">
                    <h6 class="text-muted">Đặt phòng đã xác nhận</h6>
                    <h3 class="text-success">{{ $stats['confirmed_bookings'] }}</h3>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="#recent-bookings-section" class="text-decoration-none scroll-to-section">
            <div class="card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body text-center">
                    <h6 class="text-muted">Tổng đặt phòng</h6>
                    <h3 class="text-primary">{{ $stats['total_bookings'] }}</h3>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Doanh thu 7 ngày gần đây</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyRevenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Doanh thu 6 tháng gần đây</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4 mx-auto">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Trạng thái đặt phòng</h5>
            </div>
            <div class="card-body" style="max-height: 400px;">
                <canvas id="bookingStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@if(!auth('admin')->user()->isEmployee())
<!-- Available Rooms Today -->
<div class="row mb-4" id="available-rooms-section" style="scroll-margin-top: 20px;">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-door-open"></i> Phòng trống{{ $dateFrom && $dateTo ? ' (' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') . ')' : ' hôm nay' }} ({{ $stats['available_rooms_today'] }})</h5>
                <a href="{{ route('admin.dashboard.export.available-rooms') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Số phòng</th>
                                <th>Loại</th>
                                <th>Giá/đêm</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($availableRoomsList as $room)
                                @php
                                    // Xác định route tạo booking dựa trên role
                                    // Không truyền ngày từ filter, để mặc định (hôm nay và ngày mai)
                                    if(auth('admin')->user()->isEmployee()) {
                                        $bookingRoute = route('admin.employee.bookings.create', [
                                            'room_id' => $room->id
                                        ]);
                                    } else {
                                        $bookingRoute = route('admin.bookings.create', [
                                            'room_id' => $room->id
                                        ]);
                                    }
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location.href='{{ $bookingRoute }}'" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''">
                                    <td>
                                        <strong>{{ $room->room_number }}</strong>
                                    </td>
                                    <td>{{ $room->room_type }}</td>
                                    <td>{{ number_format($room->price_per_night) }} ₫</td>
                                    <td>
                                        <span class="badge bg-success">Trống</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Không có phòng trống</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4" id="occupied-rooms-section" style="scroll-margin-top: 20px;">
        <div class="card">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-bed"></i> Phòng có khách (Chưa thanh toán){{ $dateFrom && $dateTo ? ' (' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') . ')' : ' hôm nay' }} ({{ $stats['occupied_rooms_today'] }})</h5>
                </div>
                <a href="{{ route('admin.dashboard.export.occupied-rooms') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Số phòng</th>
                                <th>Khách hàng</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($occupiedRoomsList as $room)
                                @foreach($room->bookings as $booking)
                                    <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.bookings.show', $booking->id) }}'" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''">
                                        <td>
                                            <strong>{{ $room->room_number }}</strong>
                                        </td>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                                        <td>{{ $booking->check_out_date->format('d/m/Y') }}</td>
                                        <td>
                                            @if($booking->status == 'checked_in')
                                                <span class="badge bg-info">Đã nhận phòng</span>
                                            @elseif($booking->status == 'confirmed')
                                                <span class="badge bg-success">Đã xác nhận</span>
                                            @else
                                                <span class="badge bg-warning">Chờ xử lý</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Chưa thanh toán</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">-</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không có phòng nào có khách</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Phòng đã thanh toán -->
    <div class="col-md-6 mb-4" id="paid-rooms-section" style="scroll-margin-top: 20px;">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Phòng đã thanh toán{{ $dateFrom && $dateTo ? ' (' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') . ')' : ' hôm nay' }} ({{ $paidRoomsToday ?? 0 }})</h5>
                    @if(isset($paidRoomsRevenue))
                        <small class="opacity-75">Tổng doanh thu: <strong>{{ number_format($paidRoomsRevenue) }} VNĐ</strong></small>
                    @endif
                </div>
                <a href="{{ route('admin.dashboard.export.paid-rooms', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Số phòng</th>
                                <th>Khách hàng</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paidRoomsList ?? [] as $room)
                                @if($room->bookings && $room->bookings->count() > 0)
                                    @foreach($room->bookings as $booking)
                                        <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.bookings.show', $booking->id) }}'" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''">
                                            <td>
                                                <strong>{{ $room->room_number }}</strong>
                                            </td>
                                            <td>{{ $booking->user->name ?? '-' }}</td>
                                            <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                                            <td>{{ $booking->check_out_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($booking->status == 'checked_in')
                                                    <span class="badge bg-info">Đã nhận phòng</span>
                                                @elseif($booking->status == 'confirmed')
                                                    <span class="badge bg-success">Đã xác nhận</span>
                                                @else
                                                    <span class="badge bg-warning">Chờ xử lý</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Đã thanh toán</span>
                                            </td>
                                            <td>
                                                @if($booking->payment && $booking->payment->amount)
                                                    <strong class="text-success">{{ number_format($booking->payment->amount) }} VNĐ</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không có phòng nào đã thanh toán</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth('admin')->user()->isEmployee() && isset($currentShift) && $currentShift)
<!-- Báo cáo đơn giản cho nhân viên -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Booking trong ca</h6>
                <h3 class="mb-0">{{ $currentShift->bookings()->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Doanh thu ca</h6>
                @php
                    $revenueData = $currentShift->calculateRevenue();
                    $shiftRevenue = is_array($revenueData) ? ($revenueData['total_revenue'] ?? 0) : $revenueData;
                @endphp
                <h3 class="mb-0">{{ number_format($shiftRevenue) }} VNĐ</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Check-in / Check-out</h6>
                <h3 class="mb-0">
                    {{ $currentShift->bookings()->where('status', 'checked_in')->count() }} / 
                    {{ $currentShift->bookings()->where('status', 'completed')->count() }}
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Đã hủy</h6>
                <h3 class="mb-0">{{ $currentShift->bookings()->where('status', 'cancelled')->count() }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

@if(!auth('admin')->user()->isEmployee() && isset($shiftReports) && count($shiftReports) > 0)
<!-- Báo cáo chi tiết ca làm việc -->
<div class="card mb-4" id="shift-reports-section" style="scroll-margin-top: 20px;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Báo cáo chi tiết ca làm việc</h5>
        <small class="text-muted">
            @if($dateFrom && $dateTo)
                Từ {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @else
                7 ngày gần đây
            @endif
        </small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Ngày</th>
                        <th>Nhân viên</th>
                        <th>Ca làm việc</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Số booking</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Hủy phòng</th>
                        <th>Doanh thu</th>
                        <th>Đã hoàn tiền</th>
                        <th>Doanh thu thực</th>
                        <th>Tiền mặt</th>
                        <th>Thẻ/Chuyển khoản</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shiftReports as $report)
                        @php
                            $shift = $report['shift'];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($shift->shift_date)->format('d/m/Y') }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                    <div>
                                        <strong>{{ $shift->admin->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $shift->admin->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $shift->getShiftTypeName() }}</span>
                            </td>
                            <td>
                                <small>{{ $shift->start_time }} - {{ $shift->end_time }}</small>
                            </td>
                            <td>
                                @if($shift->status == 'active')
                                    <span class="badge bg-success">Đang làm</span>
                                @elseif($shift->status == 'completed')
                                    <span class="badge bg-secondary">Hoàn thành</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($shift->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ $report['booking_count'] }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $report['checked_in_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $report['checked_out_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $report['cancelled_count'] ?? 0 }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">{{ number_format($report['revenue']) }} VNĐ</strong>
                            </td>
                            <td class="text-end">
                                @if(($report['refunded_amount'] ?? 0) > 0)
                                    <strong class="text-danger">-{{ number_format($report['refunded_amount']) }} VNĐ</strong>
                                @else
                                    <span class="text-muted">0 VNĐ</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-primary">{{ number_format($report['actual_revenue'] ?? $report['revenue']) }} VNĐ</strong>
                            </td>
                            <td class="text-end">
                                <small class="text-muted">{{ number_format($report['cash_amount']) }} VNĐ</small>
                            </td>
                            <td class="text-end">
                                <small class="text-muted">{{ number_format($report['card_amount'] + $report['transfer_amount']) }} VNĐ</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Tổng cộng:</th>
                        <th class="text-center">{{ collect($shiftReports)->sum('booking_count') }}</th>
                        <th class="text-center">{{ collect($shiftReports)->sum('checked_in_count') }}</th>
                        <th class="text-center">{{ collect($shiftReports)->sum('checked_out_count') }}</th>
                        <th class="text-center">{{ collect($shiftReports)->sum('cancelled_count') }}</th>
                        <th class="text-end">
                            <strong class="text-success">{{ number_format(collect($shiftReports)->sum('revenue')) }} VNĐ</strong>
                        </th>
                        <th class="text-end">
                            <strong class="text-danger">-{{ number_format(collect($shiftReports)->sum('refunded_amount')) }} VNĐ</strong>
                        </th>
                        <th class="text-end">
                            <strong class="text-primary">{{ number_format(collect($shiftReports)->sum('actual_revenue')) }} VNĐ</strong>
                        </th>
                        <th class="text-end">
                            <small class="text-muted">{{ number_format(collect($shiftReports)->sum('cash_amount')) }} VNĐ</small>
                        </th>
                        <th class="text-end">
                            <small class="text-muted">{{ number_format(collect($shiftReports)->sum('card_amount') + collect($shiftReports)->sum('transfer_amount')) }} VNĐ</small>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if(count($shiftReports) == 0)
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có ca làm việc nào trong khoảng thời gian đã chọn.</p>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Recent Bookings -->
<div class="card" id="recent-bookings-section" style="scroll-margin-top: 20px;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> 
            @if(auth('admin')->user()->isEmployee())
                Danh sách đặt phòng
            @else
                Đặt phòng gần đây
            @endif
        </h5>
        @if(!auth('admin')->user()->isEmployee())
        <a href="{{ route('admin.dashboard.export.recent-bookings') }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $booking)
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>{{ $booking->user->name }}</td>
                            <td>{{ $booking->room->room_number }}</td>
                            <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                            <td>{{ $booking->check_out_date->format('d/m/Y') }}</td>
                            <td>{{ number_format($booking->total_price) }} VNĐ</td>
                            <td>
                                @if($booking->status == 'pending')
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @elseif($booking->status == 'confirmed')
                                    <span class="badge bg-success">Đã xác nhận</span>
                                @elseif($booking->status == 'checked_in')
                                    <span class="badge bg-info">Đã nhận phòng</span>
                                @elseif($booking->status == 'checked_out')
                                    <span class="badge bg-secondary">Đã trả phòng</span>
                                @else
                                    <span class="badge bg-danger">Đã hủy</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->payment && $booking->payment->payment_status == 'completed')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @else
                                    <span class="badge bg-danger">Chưa thanh toán</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có đặt phòng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Validation date range
    document.addEventListener('DOMContentLoaded', function() {
        const dateFromInput = document.getElementById('dateFrom');
        const dateToInput = document.getElementById('dateTo');
        const filterForm = document.getElementById('filterForm');
        const dateToError = document.getElementById('dateToError');
        
        // Validation khi submit form
        filterForm.addEventListener('submit', function(e) {
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;
            
            if (dateFrom && dateTo && dateTo < dateFrom) {
                e.preventDefault();
                dateToInput.classList.add('is-invalid');
                dateToError.style.display = 'block';
                dateToInput.focus();
                return false;
            } else {
                dateToInput.classList.remove('is-invalid');
                dateToError.style.display = 'none';
            }
        });
        
        // Validation real-time khi thay đổi
        dateToInput.addEventListener('change', function() {
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;
            
            if (dateFrom && dateTo && dateTo < dateFrom) {
                dateToInput.classList.add('is-invalid');
                dateToError.style.display = 'block';
            } else {
                dateToInput.classList.remove('is-invalid');
                dateToError.style.display = 'none';
            }
        });
        
        dateFromInput.addEventListener('change', function() {
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;
            
            // Tự động set min cho dateTo
            if (dateFrom) {
                dateToInput.setAttribute('min', dateFrom);
            }
            
            // Validate lại nếu dateTo đã có giá trị
            if (dateTo && dateTo < dateFrom) {
                dateToInput.classList.add('is-invalid');
                dateToError.style.display = 'block';
            } else {
                dateToInput.classList.remove('is-invalid');
                dateToError.style.display = 'none';
            }
        });
        
        // Set min cho dateTo khi load trang
        if (dateFromInput.value) {
            dateToInput.setAttribute('min', dateFromInput.value);
        }
    });
    
    // Xử lý scroll smooth khi click vào card
    document.addEventListener('DOMContentLoaded', function() {
        const scrollLinks = document.querySelectorAll('.scroll-to-section');
        
        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                
                if (targetId && targetId.startsWith('#')) {
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // Scroll smooth đến element với offset
                        const headerOffset = 80;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Highlight section bằng cách thêm border tạm thời
                        const card = targetElement.querySelector('.card');
                        if (card) {
                            card.style.transition = 'box-shadow 0.3s, border 0.3s';
                            card.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.5)';
                            card.style.border = '2px solid #0d6efd';
                            
                            setTimeout(() => {
                                card.style.boxShadow = '';
                                card.style.border = '';
                            }, 2000);
                        }
                    }
                }
            });
        });
    });
</script>
<script>
    // Daily Revenue Chart (7 days)
    const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
    const dailyRevenueChart = new Chart(dailyRevenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($dailyRevenue, 'date')) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode(array_column($dailyRevenue, 'revenue')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + ' ₫';
                        }
                    }
                }
            }
        }
    });

    // Monthly Revenue Chart (6 months)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode(array_column($monthlyRevenue, 'revenue')) !!},
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + ' ₫';
                        }
                    }
                }
            }
        }
    });

    // Booking Status Chart
    const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đã xác nhận', 'Đã nhận phòng', 'Đã trả phòng', 'Đã hủy'],
            datasets: [{
                data: [
                    {{ $bookingStatus['pending'] }},
                    {{ $bookingStatus['confirmed'] }},
                    {{ $bookingStatus['checked_in'] }},
                    {{ $bookingStatus['checked_out'] }},
                    {{ $bookingStatus['cancelled'] }}
                ],
                backgroundColor: [
                    'rgb(255, 193, 7)',
                    'rgb(40, 167, 69)',
                    'rgb(23, 162, 184)',
                    'rgb(108, 117, 125)',
                    'rgb(220, 53, 69)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 8,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

