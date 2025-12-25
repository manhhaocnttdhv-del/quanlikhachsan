@extends('layouts.app')

@section('title', 'Đặt phòng của tôi')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 0;
        margin-bottom: 50px;
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,101.3C1248,85,1344,75,1392,69.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
        background-size: cover;
        opacity: 0.3;
    }
    .booking-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 30px;
        background: white;
    }
    .booking-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
    }
    .booking-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-bottom: 3px solid #667eea;
    }
    .booking-body {
        padding: 25px;
    }
    .room-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
    }
    .room-placeholder {
        width: 100%;
        height: 150px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .info-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 15px;
    }
    .status-badge {
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
    }
    .price-highlight {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        text-align: center;
        margin-top: 15px;
    }
    .btn-action {
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none;
        position: relative;
        overflow: hidden;
    }
    .btn-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    .btn-action:hover::before {
        width: 300px;
        height: 300px;
    }
    .btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .btn-view:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .btn-cancel {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    .btn-cancel:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
        color: white;
    }
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }
    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }
    .filter-tabs {
        background: white;
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .filter-tabs .nav-link {
        border-radius: 10px;
        font-weight: 600;
        color: #6c757d;
        transition: all 0.3s;
    }
    .filter-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Đặt phòng của tôi</h1>
        <p class="lead">Quản lý tất cả các đặt phòng của bạn</p>
    </div>
</div>

<!-- Thông báo hoàn tiền nếu có -->
@php
    $hasRefundedPayment = $bookings->contains(function($booking) {
        return $booking->payment && $booking->payment->payment_status == 'refunded';
    });
@endphp

@if($hasRefundedPayment)
    <div class="container mt-4">
        <div class="alert alert-success border-success shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                <div>
                    <h6 class="alert-heading mb-1">
                        <i class="fas fa-money-bill-wave me-2"></i>Bạn có đặt phòng đã được hoàn tiền
                    </h6>
                    <p class="mb-0 small">
                        Vui lòng kiểm tra chi tiết đặt phòng để xem thông tin hoàn tiền.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="container pb-5">
    @forelse($bookings as $booking)
        <div class="booking-card">
            <div class="booking-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">
                            <i class="fas fa-door-open me-2 text-primary"></i>
                            Phòng {{ $booking->room->room_number }}
                        </h5>
                        <small class="text-muted">Mã đặt phòng: #{{ $booking->id }}</small>
                    </div>
                    <div>
                        @if($booking->status == 'pending')
                            <span class="status-badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i>Chờ xác nhận
                            </span>
                        @elseif($booking->status == 'confirmed')
                            <span class="status-badge bg-success text-white">
                                <i class="fas fa-check-circle me-1"></i>Đã xác nhận
                            </span>
                        @elseif($booking->status == 'checked_in')
                            <span class="status-badge bg-info text-white">
                                <i class="fas fa-sign-in-alt me-1"></i>Đã nhận phòng
                            </span>
                        @elseif($booking->status == 'checked_out')
                            <span class="status-badge bg-secondary text-white">
                                <i class="fas fa-sign-out-alt me-1"></i>Đã trả phòng
                            </span>
                        @else
                            <span class="status-badge bg-danger text-white">
                                <i class="fas fa-times-circle me-1"></i>Đã hủy
                            </span>
                        @endif
                        
                        <!-- Badge hoàn tiền -->
                        @if($booking->payment && $booking->payment->payment_status == 'refunded')
                            <span class="status-badge bg-info text-white ms-2">
                                <i class="fas fa-money-bill-wave me-1"></i>Đã hoàn tiền
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="booking-body">
                <div class="row">
                    <div class="col-md-3 mb-3 mb-md-0">
                        @if($booking->room->image)
                            <img src="{{ asset('storage/' . $booking->room->image) }}" 
                                 class="room-image" alt="Phòng {{ $booking->room->room_number }}" width="300" height="200">
                        @else
                            @if ($booking->room->images && count($booking->room->images) > 0 && isset($booking->room->images[0]))
                                <img src="{{ asset('storage/' . $booking->room->images[0]->image_path) }}"  width="300" height="200"
                                 class="room-image" alt="Phòng {{ $booking->room->room_number }}">
                            @else
                             <div class="room-placeholder text-white">
                                <i class="fas fa-bed fa-3x opacity-75"></i>
                            </div>
                            @endif
                           
                        @endif
                        <div class="text-center mt-2">
                            <span class="badge bg-primary rounded-pill">{{ $booking->room->room_type }}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Thời gian</small>
                                <strong>
                                    {{ $booking->check_in_date->format('d/m/Y') }}
                                        @if($booking->check_in_time)
                                            <span class="text-muted">({{ substr($booking->check_in_time, 0, 5) }})</span>
                                        @endif
                                    - 
                                    {{ $booking->check_out_date->format('d/m/Y') }}
                                        @if($booking->check_out_time)
                                            <span class="text-muted">({{ substr($booking->check_out_time, 0, 5) }})</span>
                                        @endif
                                </strong>
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
                                <span class="badge bg-light text-dark ms-2">
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
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Số người</small>
                                <strong>{{ $booking->number_of_guests }} người</strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Ngày đặt</small>
                                <strong>{{ $booking->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="price-highlight">
                            <small class="opacity-75 d-block mb-2">Tổng thanh toán</small>
                            <h3 class="mb-0 fw-bold">{{ number_format($booking->total_price) }} ₫</h3>
                            @if($booking->payment)
                                <div class="mt-2">
                                    @if($booking->payment->payment_status == 'completed')
                                        <small class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Đã thanh toán
                                        </small>
                                    @elseif($booking->payment->payment_status == 'pending')
                                        <small class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Chờ xử lý
                                        </small>
                                    @elseif($booking->payment->payment_status == 'refunded')
                                        <small class="badge bg-info">
                                            <i class="fas fa-money-bill-wave me-1"></i>Đã hoàn tiền
                                        </small>
                                    @elseif($booking->payment->payment_status == 'failed')
                                        <small class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Thất bại
                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('user.bookings.show', $booking->id) }}" class="btn btn-action btn-view">
                                <i class="fas fa-eye me-2"></i>Chi tiết
                            </a>
                            
                            @if(in_array($booking->status, ['pending', 'confirmed']))
                                <form action="{{ route('user.bookings.cancel', $booking->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-action btn-cancel w-100" 
                                            onclick="return confirm('Bạn có chắc muốn hủy đặt phòng này?')">
                                        <i class="fas fa-times me-2"></i>Hủy đặt phòng
                                    </button>
                                </form>
                            @endif

                            @if($booking->status === 'checked_out' && !$booking->review)
                                <a href="{{ route('user.reviews.create', ['room_id' => $booking->room_id, 'booking_id' => $booking->id]) }}" 
                                   class="btn btn-warning w-100">
                                    <i class="fas fa-star me-2"></i>Đánh giá phòng
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times fa-3x text-white"></i>
            </div>
            <h3 class="mb-3">Chưa có đặt phòng nào</h3>
            <p class="text-muted mb-4">Bạn chưa có đặt phòng nào. Hãy khám phá và đặt phòng ngay!</p>
            <a href="{{ route('rooms.index') }}" class="btn btn-view btn-action btn-lg">
                <i class="fas fa-search me-2"></i>Tìm phòng ngay
            </a>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($bookings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection

