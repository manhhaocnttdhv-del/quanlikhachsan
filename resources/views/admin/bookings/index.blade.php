@extends('layouts.admin')

@section('title', 'Quản lý Đặt phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Quản lý Đặt phòng</h2>
    <div>
        @if(auth('admin')->user()->isAdmin())
        <a href="{{ route('admin.bookings.export', request()->all()) }}" class="btn btn-success me-2">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        @endif
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo đặt phòng
        </a>
    </div>
</div>

@php
    $totalBookings = \App\Models\Booking::count();
    $pendingCount = \App\Models\Booking::where('status', 'pending')->count();
    $confirmedCount = \App\Models\Booking::where('status', 'confirmed')->count();
    $checkedInCount = \App\Models\Booking::where('status', 'checked_in')->count();
    $checkedOutCount = \App\Models\Booking::where('status', 'checked_out')->count();
    $cancelledCount = \App\Models\Booking::where('status', 'cancelled')->count();
@endphp

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng số</h6>
                        <h3 class="mb-0">{{ $totalBookings }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card pending h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Chờ xử lý</h6>
                        <h3 class="mb-0 text-warning">{{ $pendingCount }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card confirmed h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã xác nhận</h6>
                        <h3 class="mb-0 text-success">{{ $confirmedCount }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card checked_in h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã nhận phòng</h6>
                        <h3 class="mb-0 text-info">{{ $checkedInCount }}</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card checked_out h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã trả phòng</h6>
                        <h3 class="mb-0 text-secondary">{{ $checkedOutCount }}</h3>
                    </div>
                    <div class="text-secondary">
                        <i class="fas fa-door-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card cancelled h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã hủy</h6>
                        <h3 class="mb-0 text-danger">{{ $cancelledCount }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card filter-card mb-4 border-0 shadow-lg">
    <div class="card-body p-4">
        <h5 class="mb-3 text-white"><i class="fas fa-filter"></i> Bộ lọc tìm kiếm</h5>
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-white small">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Đã nhận phòng</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Đã trả phòng</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-7">
                <label class="form-label text-white small">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm kiếm theo tên khách hàng, email, số phòng...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-light w-100">
                    <i class="fas fa-search"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card booking-card border-0 shadow-lg">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách đặt phòng</h5>
            <span class="badge bg-primary">{{ $bookings->total() }} đặt phòng</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Số đêm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th style="width: 150px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">#{{ $booking->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ strtoupper(substr($booking->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $booking->user->name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope"></i> {{ $booking->user->email }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-bed"></i> {{ $booking->room->room_number }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $booking->room->room_type }}</small>
                            </td>
                            <td>
                                <i class="fas fa-calendar-check text-success"></i>
                                <strong>{{ $booking->check_in_date->format('d/m/Y') }}</strong>
                                @if($booking->check_in_time)
                                    <br><small class="text-muted">{{ substr($booking->check_in_time, 0, 5) }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-calendar-times text-danger"></i>
                                <strong>{{ $booking->check_out_date->format('d/m/Y') }}</strong>
                                @if($booking->check_out_time)
                                    <br><small class="text-muted">{{ substr($booking->check_out_time, 0, 5) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    // Format date thành Y-m-d trước khi nối với time
                                    $checkInDateStr = $booking->check_in_date->format('Y-m-d');
                                    $checkOutDateStr = $booking->check_out_date->format('Y-m-d');
                                    // Lấy phần H:i từ time string (bỏ seconds nếu có)
                                    $checkInTime = $booking->check_in_time ? substr($booking->check_in_time, 0, 5) : '14:00';
                                    $checkOutTime = $booking->check_out_time ? substr($booking->check_out_time, 0, 5) : '12:00';
                                    
                                    $checkIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $checkInDateStr . ' ' . $checkInTime);
                                    $checkOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $checkOutDateStr . ' ' . $checkOutTime);
                                    $isSameDay = $checkIn->isSameDay($checkOut);
                                @endphp
                                <span class="badge bg-light text-dark">
                                    @if($isSameDay)
                                        {{ $checkIn->diffInHours($checkOut) }} giờ
                                    @else
                                        @php
                                            // Tính số đêm dựa trên cả date và time
                                            $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
                                            
                                            // Nếu check-out time >= check-in time, tính thêm 1 đêm (đêm cuối)
                                            if ($checkInTime && $checkOutTime && strpos($checkInTime, ':') !== false && strpos($checkOutTime, ':') !== false) {
                                                $checkInTimeParts = explode(':', $checkInTime);
                                                $checkOutTimeParts = explode(':', $checkOutTime);
                                                $checkInHour = (int)$checkInTimeParts[0];
                                                $checkInMinute = (int)$checkInTimeParts[1];
                                                $checkOutHour = (int)$checkOutTimeParts[0];
                                                $checkOutMinute = (int)$checkOutTimeParts[1];
                                                
                                                if ($checkOutHour > $checkInHour || ($checkOutHour == $checkInHour && $checkOutMinute >= $checkInMinute)) {
                                                    $nights += 1;
                                                }
                                            } else {
                                                $nights += 1;
                                            }
                                            
                                            // Nếu check-out time > 12:00, tính thêm 0.5 đêm (late check-out)
                                            if ($checkOutTime && strpos($checkOutTime, ':') !== false) {
                                                $timeParts = explode(':', $checkOutTime);
                                                $hour = (int)$timeParts[0];
                                                if ($hour > 12) {
                                                    $nights += 0.5;
                                                }
                                            }
                                        @endphp
                                        {{ $nights }} {{ $nights % 1 === 0 ? 'đêm' : 'đêm' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <strong class="text-primary">{{ number_format($booking->total_price) }} VNĐ</strong>
                            </td>
                            <td>
                                @if($booking->status == 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i> Chờ xử lý
                                    </span>
                                @elseif($booking->status == 'confirmed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Đã xác nhận
                                    </span>
                                @elseif($booking->status == 'checked_in')
                                    <span class="badge bg-info">
                                        <i class="fas fa-key"></i> Đã nhận phòng
                                    </span>
                                @elseif($booking->status == 'checked_out')
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-door-open"></i> Đã trả phòng
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Đã hủy
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($booking->payment && $booking->payment->payment_status == 'completed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($booking->status !== 'cancelled')
                                    <a href="{{ route('admin.bookings.edit', $booking->id) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$booking->payment || $booking->payment->payment_status != 'completed')
                                    <a href="{{ route('admin.payments.create', ['booking_id' => $booking->id]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Thanh toán">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete({{ $booking->id }})"
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                                <form id="delete-form-{{ $booking->id }}" 
                                      action="{{ route('admin.bookings.destroy', $booking->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <p class="mb-0">Chưa có đặt phòng nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Hiển thị {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} trong tổng số {{ $bookings->total() }} đặt phòng
            </div>
            <div>
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-left: 4px solid;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    .stats-card.pending { border-left-color: #ffc107; }
    .stats-card.confirmed { border-left-color: #28a745; }
    .stats-card.checked_in { border-left-color: #17a2b8; }
    .stats-card.checked_out { border-left-color: #6c757d; }
    .stats-card.cancelled { border-left-color: #dc3545; }
    
    .table-hover tbody tr {
        transition: background-color 0.2s;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .booking-card {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
    }
    .filter-card .card-body {
        color: white;
    }
    .filter-card .form-select,
    .filter-card .form-control {
        background-color: rgba(255,255,255,0.95);
        border: none;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Bạn có chắc muốn xóa đặt phòng này?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush

