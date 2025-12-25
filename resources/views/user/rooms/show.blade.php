@extends('layouts.app')

@section('title', 'Chi tiết phòng ' . $room->room_number)

@push('styles')
<style>
    #roomImageCarousel {
        border-radius: 10px 10px 0 0;
        overflow: hidden;
    }
    #roomImageCarousel .carousel-item img {
        transition: transform 0.3s;
    }
    #roomImageCarousel:hover .carousel-item img {
        transform: scale(1.05);
    }
    .carousel-indicators button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    .carousel-indicators button.active {
        background-color: #667eea;
        border-color: #667eea;
    }
    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.7;
        transition: all 0.3s;
    }
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .carousel-control-prev {
        left: 20px;
    }
    .carousel-control-next {
        right: 20px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">Phòng</a></li>
            <li class="breadcrumb-item active">Phòng {{ $room->room_number }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                @php
                    $primaryImage = $room->primaryImage;
                    $allImages = $room->images;
                    
                    // Sắp xếp: ảnh chính đầu tiên, sau đó theo order
                    $sortedImages = $allImages->sortBy(function($img) {
                        return $img->is_primary ? 0 : ($img->order + 1);
                    });
                    
                    $hasMultipleImages = $sortedImages->count() > 1;
                @endphp

                @if($sortedImages->count() > 0)
                    @if($hasMultipleImages)
                        <!-- Slider cho nhiều ảnh -->
                        <div id="roomImageCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($sortedImages as $index => $img)
                                    <button type="button" data-bs-target="#roomImageCarousel" data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($sortedImages as $index => $img)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" 
                                             class="d-block w-100" 
                                             alt="Phòng {{ $room->room_number }} - Ảnh {{ $index + 1 }}"
                                             style="height: 500px; object-fit: cover;">
                                        @if($img->is_primary)
                                            <div class="position-absolute top-0 start-0 m-3">
                                                <span class="badge bg-success fs-6 shadow">
                                                    <i class="fas fa-star me-1"></i>Ảnh chính
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomImageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomImageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    @else
                        <!-- Chỉ 1 ảnh -->
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $sortedImages->first()->image_path) }}" 
                                 class="card-img-top" 
                                 alt="{{ $room->room_number }}"
                                 style="height: 500px; object-fit: cover;">
                            @if($sortedImages->first()->is_primary)
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-success fs-6 shadow">
                                        <i class="fas fa-star me-1"></i>Ảnh chính
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                @elseif($room->image)
                    <!-- Fallback: ảnh cũ từ field image -->
                    <img src="{{ asset('storage/' . $room->image) }}" 
                         class="card-img-top" 
                         alt="{{ $room->room_number }}"
                         style="height: 500px; object-fit: cover;">
                @else
                    <!-- Không có ảnh -->
                    <div class="bg-gradient text-white text-center" 
                         style="height: 500px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div>
                            <i class="fas fa-bed fa-5x mb-3"></i>
                            <p class="fs-4">Phòng {{ $room->room_number }}</p>
                        </div>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Phòng {{ $room->room_number }}</h3>
                        @if($room->status == 'available')
                            <span class="badge bg-success fs-6">Còn trống</span>
                        @elseif($room->status == 'occupied')
                            <span class="badge bg-danger fs-6">Đã đặt</span>
                        @else
                            <span class="badge bg-warning fs-6">Bảo trì</span>
                        @endif
                    </div>

                    <div class="mb-4">
                        <span class="badge bg-primary fs-6">{{ $room->room_type }}</span>
                        <span class="badge bg-info fs-6"><i class="fas fa-users"></i> {{ $room->capacity }} người</span>
                    </div>

                    <h4 class="text-primary mb-4">
                        {{ number_format($room->price_per_night) }} VNĐ / đêm
                    </h4>

                    <h5 class="mb-3">Mô tả</h5>
                    <p class="text-muted">{{ $room->description }}</p>

                    @if($room->amenities)
                        <h5 class="mb-3 mt-4">Tiện nghi</h5>
                        <div class="row">
                            @foreach($room->amenities as $amenity)
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check-circle text-success"></i> {{ $amenity }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Đặt phòng ngay</h5>
                    
                    @auth
                        @if($room->status == 'available')
                            <form action="{{ route('user.bookings.create') }}" method="GET">
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                
                                <div class="mb-3">
                                    <label class="form-label">Ngày nhận phòng</label>
                                    <input type="date" name="check_in_date" class="form-control" 
                                           min="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày trả phòng</label>
                                    <input type="date" name="check_out_date" class="form-control" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số người</label>
                                    <input type="number" name="number_of_guests" class="form-control" 
                                           min="1" max="{{ $room->capacity }}" value="1" required>
                                    <small class="text-muted">Tối đa {{ $room->capacity }} người</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-check"></i> Đặt phòng ngay
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Phòng hiện không khả dụng
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Vui lòng <a href="{{ route('user.login') }}">đăng nhập</a> để đặt phòng
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Phần đánh giá -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-star text-warning"></i> Đánh giá
                            @php
                                $approvedReviews = $room->approvedReviews;
                                $avgRating = $approvedReviews->avg('rating');
                                $totalReviews = $approvedReviews->count();
                            @endphp
                            @if($totalReviews > 0)
                                <span class="badge bg-primary ms-2">{{ number_format($avgRating, 1) }}/5 ({{ $totalReviews }} đánh giá)</span>
                            @endif
                        </h5>
                        @auth
                            @php
                                $userReview = \App\Models\Review::where('user_id', auth()->id())
                                    ->where('room_id', $room->id)
                                    ->first();
                            @endphp
                            @if(!$userReview)
                                <a href="{{ route('user.reviews.create', ['room_id' => $room->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Viết đánh giá
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="card-body">
                    @if($approvedReviews->count() > 0)
                        @foreach($approvedReviews->take(5) as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $review->user->name }}</h6>
                                        <div>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                            <span class="ms-2 text-muted small">{{ $review->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="mb-0">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach

                        @if($totalReviews > 5)
                            <div class="text-center">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    Xem tất cả {{ $totalReviews }} đánh giá
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Chưa có đánh giá nào cho phòng này</p>
                            @auth
                                @php
                                    $userReview = \App\Models\Review::where('user_id', auth()->id())
                                        ->where('room_id', $room->id)
                                        ->first();
                                @endphp
                                @if(!$userReview)
                                    <a href="{{ route('user.reviews.create', ['room_id' => $room->id]) }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Viết đánh giá đầu tiên
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

