@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        margin-bottom: 40px;
    }
    .stat-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        transition: all 0.3s;
        margin-bottom: 25px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    .booking-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        transition: all 0.3s;
    }
    .booking-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    }
    .booking-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px 20px;
        border-bottom: 2px solid #667eea;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Dashboard</h1>
        <p class="lead">Tổng quan hoạt động của bạn</p>
    </div>
</div>

<div class="container pb-5">
    <!-- Stats Cards -->
    <div class="row mb-5">
        <div class="col-md-3 mb-4">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Tổng đặt phòng</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_bookings'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Chờ xác nhận</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['pending_bookings'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Đã xác nhận</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['confirmed_bookings'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" class="me-3">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Tổng đã chi</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_spent']) }} ₫</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2 text-primary"></i>Đặt phòng gần đây
                        </h5>
                        <a href="{{ route('user.bookings.index') }}" class="btn btn-sm btn-outline-primary">
                            Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($recentBookings as $booking)
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            <i class="fas fa-door-open me-2 text-primary"></i>
                                            Phòng {{ $booking->room->room_number }}
                                        </h6>
                                        <small class="text-muted">#{{ $booking->id }} • {{ $booking->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div>
                                        @if($booking->status == 'pending')
                                            <span class="badge bg-warning">Chờ xác nhận</span>
                                        @elseif($booking->status == 'confirmed')
                                            <span class="badge bg-success">Đã xác nhận</span>
                                        @elseif($booking->status == 'checked_in')
                                            <span class="badge bg-info">Đã nhận phòng</span>
                                        @elseif($booking->status == 'checked_out')
                                            <span class="badge bg-secondary">Đã trả phòng</span>
                                        @else
                                            <span class="badge bg-danger">Đã hủy</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                            <strong>{{ $booking->check_in_date->format('d/m/Y') }}</strong> - 
                                            <strong>{{ $booking->check_out_date->format('d/m/Y') }}</strong>
                                        </p>
                                        <p class="mb-2">
                                            <i class="fas fa-users me-2 text-muted"></i>
                                            {{ $booking->number_of_guests }} người
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <h5 class="text-primary mb-0">{{ number_format($booking->total_price) }} ₫</h5>
                                        @if($booking->payment)
                                            <small class="text-muted">
                                                @if($booking->payment->payment_status == 'completed')
                                                    <i class="fas fa-check-circle text-success"></i> Đã thanh toán
                                                @else
                                                    <i class="fas fa-clock text-warning"></i> Chờ thanh toán
                                                @endif
                                            </small>
                                        @else
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> Chưa thanh toán
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('user.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có đặt phòng nào</p>
                            <a href="{{ route('rooms.index') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Tìm phòng ngay
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings & Quick Actions -->
        <div class="col-md-4">
            <!-- Upcoming Bookings -->
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-alt me-2 text-success"></i>Sắp tới
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($upcomingBookings as $booking)
                        <div class="border-bottom pb-3 mb-3">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-door-open me-2 text-primary"></i>
                                Phòng {{ $booking->room->room_number }}
                            </h6>
                            <p class="mb-1 small">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $booking->check_in_date->format('d/m/Y') }}
                            </p>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Còn {{ now()->diffInDays($booking->check_in_date) }} ngày
                            </p>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>Không có đặt phòng sắp tới
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bolt me-2 text-warning"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rooms.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Tìm phòng
                        </a>
                        <a href="{{ route('user.bookings.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check me-2"></i>Đặt phòng của tôi
                        </a>
                        <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>Thông tin cá nhân
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

