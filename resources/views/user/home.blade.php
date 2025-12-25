@extends('layouts.app')

@section('title', 'Trang chủ - Hotel Management')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,101.3C1248,85,1344,75,1392,69.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
        background-size: cover;
    }
    .feature-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .feature-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .room-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        height: 100%;
    }
    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .room-card img {
        height: 200px;
        object-fit: cover;
    }
    .room-card .placeholder-img {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="hero-section text-white">
    <div class="container text-center position-relative">
        <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInDown">
           Khám Phá Kỳ Nghỉ Sang Trọng
        </h1>
        <p class="lead mb-4 fs-4 animate__animated animate__fadeInUp">
            Trải nghiệm kỳ nghỉ tuyệt vời với dịch vụ 5 sao và tiện nghi hiện đại
        </p>
        <a href="{{ route('rooms.index') }}" class="btn btn-light btn-lg px-5 py-3 rounded-pill shadow-lg animate__animated animate__fadeInUp">
            <i class="fas fa-search me-2"></i> Khám phá phòng
        </a>
    </div>
</div>

<!-- Features Section -->
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold mb-3">Tại sao chọn chúng tôi?</h2>
        <p class="text-muted">Trải nghiệm dịch vụ tốt nhất với các tiện ích hiện đại</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card feature-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-primary bg-opacity-10">
                        <i class="fas fa-search fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Tìm kiếm thông minh</h5>
                    <p class="card-text text-muted">Lọc phòng theo giá, loại và số người một cách dễ dàng</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-success bg-opacity-10">
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title fw-bold">Đặt phòng nhanh chóng</h5>
                    <p class="card-text text-muted">Quy trình đơn giản, xác nhận ngay lập tức</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-info bg-opacity-10">
                        <i class="fas fa-shield-alt fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thanh toán bảo mật</h5>
                    <p class="card-text text-muted">Nhiều phương thức thanh toán an toàn, tiện lợi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Rooms -->
    <div class="mt-5 pt-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Phòng nổi bật</h2>
            <p class="text-muted">Khám phá các phòng được yêu thích nhất</p>
        </div>
        <div class="row g-4">
            @forelse($featuredRooms as $room)
                <div class="col-md-4">
                    <div class="card room-card shadow-sm">
                        @php
                            $primaryImg = $room->primaryImage ?? $room->images->first();
                            $displayImage = $primaryImg ? $primaryImg->image_path : ($room->image ?? null);
                        @endphp
                        @if($displayImage)
                            <img src="{{ asset('storage/' . $displayImage) }}" class="card-img-top" alt="{{ $room->room_number }}">
                        @else
                            <div class="placeholder-img text-white">
                                <i class="fas fa-bed fa-4x"></i>
                            </div>
                        @endif
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold">Phòng {{ $room->room_number }}</h5>
                                <span class="badge bg-primary rounded-pill">{{ $room->room_type }}</span>
                            </div>
                            <p class="card-text text-muted small mb-3">{{ Str::limit($room->description, 80) }}</p>
                            
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-users text-muted me-2"></i>
                                <small class="text-muted">{{ $room->capacity }} người</small>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="text-primary mb-0">{{ number_format($room->price_per_night) }} ₫</h4>
                                    <small class="text-muted">/ đêm</small>
                                </div>
                                <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-primary rounded-pill px-4">
                                    Chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có phòng nào.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('rooms.index') }}" class="btn btn-lg btn-outline-primary rounded-pill px-5 py-3">
                Xem tất cả phòng <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>
@endsection

