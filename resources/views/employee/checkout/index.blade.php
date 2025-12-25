@extends('layouts.admin')

@section('title', 'Khách hàng Checkout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-door-open"></i> 
        @if(request('view_all') == '1')
            Danh sách Đặt phòng & Checkout (Tất cả)
        @elseif(request('checkout_date') && request('checkout_date') != '')
            Khách hàng Checkout ngày {{ \Carbon\Carbon::parse(request('checkout_date'))->format('d/m/Y') }}
        @else
            Danh sách Đặt phòng & Checkout (Hôm nay)
        @endif
    </h2>
    @if(isset($hasOtherActiveShift) && $hasOtherActiveShift)
        <button class="btn btn-primary" disabled title="Nhân viên khác đang làm việc. Không thể tạo booking.">
            <i class="fas fa-plus"></i> Đặt phòng mới
        </button>
    @else
        <a href="{{ route('admin.employee.bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Đặt phòng mới
        </a>
    @endif
</div>

@if(isset($hasOtherActiveShift) && $hasOtherActiveShift && isset($otherActiveShift))
<div class="alert alert-warning mb-4">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Lưu ý:</strong> Nhân viên <strong>{{ $otherActiveShift->admin->name }}</strong> đang làm việc ({{ $otherActiveShift->getShiftTypeName() }}). 
    Bạn không thể tạo booking hoặc thực hiện các thao tác khác lúc này. Vui lòng đợi nhân viên hiện tại kết thúc ca.
</div>
@elseif(!$hasShift)
<div class="alert alert-warning mb-4">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Lưu ý:</strong> Bạn chưa có ca làm việc hôm nay. Vui lòng liên hệ admin để được phân công ca.
</div>
@elseif(!$isActiveShift)
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle"></i> 
    <strong>Thông tin:</strong> Ca làm việc của bạn: {{ $todayShift->start_time }} - {{ $todayShift->end_time }}
    @if($todayShift->status == 'scheduled')
        (Chưa bắt đầu)
    @endif
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng checkout</h6>
                        <h3 class="mb-0">{{ $totalCheckouts }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã thanh toán</h6>
                        <h3 class="mb-0 text-success">{{ $paidCheckouts }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Chưa thanh toán</h6>
                        <h3 class="mb-0 text-danger">{{ $unpaidCheckouts }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.checkout.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Ngày checkout</label>
                <input type="date" name="checkout_date" class="form-control" 
                       value="{{ request('checkout_date') }}"
                       {{ request('view_all') == '1' ? 'disabled' : '' }}>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Tên, email, SĐT khách hàng hoặc số phòng..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái thanh toán</label>
                <select name="payment_status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="view_all" value="1" 
                           id="view_all" {{ request('view_all') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="view_all">
                        Xem tất cả
                    </label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.employee.checkout.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã đặt phòng</th>
                        <th>Khách hàng</th>
                        <th>Số phòng</th>
                        <th>Check-in</th>
                        <th>Checkout</th>
                        <th>Trạng thái thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <strong>#{{ $booking->id }}</strong>
                                @if($booking->status == 'pending')
                                    <br><span class="badge bg-warning text-dark mt-1">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @elseif($booking->status == 'confirmed')
                                    <br><span class="badge bg-success mt-1">
                                        <i class="fas fa-check-circle"></i> Confirmed
                                    </span>
                                @elseif($booking->status == 'checked_in')
                                    <br><span class="badge bg-info mt-1">
                                        <i class="fas fa-key"></i> Checked In
                                    </span>
                                @elseif($booking->status == 'completed')
                                    <br><span class="badge bg-success mt-1">
                                        <i class="fas fa-check-double"></i> Hoàn thành
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $booking->user->name }}</strong><br>
                                <small class="text-muted">{{ $booking->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $booking->room->room_number }}</span>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $booking->check_in_time ?? '14:00' }}</small>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $booking->check_out_time ?? '12:00' }}</small>
                            </td>
                            <td>
                                @if($booking->payment && $booking->payment->payment_status == 'completed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        @if($booking->payment->payment_method == 'cash')
                                            <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                        @elseif($booking->payment->payment_method == 'bank_transfer_qr')
                                            <i class="fas fa-qrcode"></i> QR
                                        @else
                                            {{ $booking->payment->payment_method }}
                                        @endif
                                    </small>
                                @elseif($booking->payment && $booking->payment->payment_status == 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i> Chờ xác nhận
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        @if($booking->payment->payment_method == 'bank_transfer_qr')
                                            <i class="fas fa-qrcode"></i> QR chờ xác nhận
                                        @else
                                            {{ $booking->payment->payment_method }}
                                        @endif
                                    </small>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($booking->total_price) }} VNĐ</strong>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                    @if($booking->payment && $booking->payment->payment_method == 'bank_transfer_qr' && $booking->payment->payment_status == 'pending')
                                        <form action="{{ route('admin.employee.payments.update', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="payment_status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Xác nhận thanh toán QR này?')"
                                                    title="Xác nhận thanh toán QR">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    @if(request('view_all') == '1')
                                        Không có đặt phòng nào
                                    @elseif(request('checkout_date') && request('checkout_date') != '')
                                        Không có khách hàng nào đến checkout ngày {{ \Carbon\Carbon::parse(request('checkout_date'))->format('d/m/Y') }}
                                    @else
                                        Không có đặt phòng nào hôm nay (check-in hoặc checkout)
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewAllCheckbox = document.getElementById('view_all');
        const checkoutDateInput = document.querySelector('input[name="checkout_date"]');
        const form = document.querySelector('form');
        
        if (viewAllCheckbox && checkoutDateInput) {
            // Khởi tạo trạng thái ban đầu
            if (viewAllCheckbox.checked) {
                checkoutDateInput.disabled = true;
                checkoutDateInput.value = '';
            }
            
            // Xử lý khi thay đổi checkbox
            viewAllCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    checkoutDateInput.disabled = true;
                    checkoutDateInput.value = '';
                } else {
                    checkoutDateInput.disabled = false;
                }
            });
        }
    });
</script>
@endpush
@endsection

