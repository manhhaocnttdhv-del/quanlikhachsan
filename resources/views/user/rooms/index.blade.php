@extends('layouts.app')

@section('title', 'Tìm kiếm phòng')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        margin-bottom: 40px;
    }
    .filter-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .filter-card .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    .room-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s;
        height: 100%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .room-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .room-card-img {
        height: 220px;
        object-fit: cover;
        width: 100%;
    }
    .room-card-placeholder {
        height: 220px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .room-type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .room-status-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .price-tag {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }
    .amenity-icon {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .btn-view-room {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-view-room:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .results-info {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Khám phá phòng của chúng tôi</h1>
        <p class="lead">Tìm phòng hoàn hảo cho kỳ nghỉ của bạn</p>
    </div>
</div>

<div class="container pb-5">
    <!-- Search Filters -->
    <div class="filter-card card">
        <div class="card-body p-4">
            <h5 class="mb-4"><i class="fas fa-filter me-2"></i>Bộ lọc tìm kiếm</h5>
            <form action="{{ route('rooms.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-door-open me-2"></i>Loại phòng</label>
                        <select name="room_type" class="form-select">
                            <option value="">Tất cả loại phòng</option>
                            <option value="Standard" {{ request('room_type') == 'Standard' ? 'selected' : '' }}>Standard</option>
                            <option value="Deluxe" {{ request('room_type') == 'Deluxe' ? 'selected' : '' }}>Deluxe</option>
                            <option value="Suite" {{ request('room_type') == 'Suite' ? 'selected' : '' }}>Suite</option>
                            <option value="VIP" {{ request('room_type') == 'VIP' ? 'selected' : '' }}>VIP</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-money-bill-wave me-2"></i>Giá tối thiểu</label>
                        <input type="number" name="min_price" class="form-control"  min="300000"
                               value="{{ request('min_price') }}" placeholder="0 VNĐ">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-money-bill-wave me-2"></i>Giá tối đa</label>
                        <input type="number" name="max_price" class="form-control"min="300000"
                               value="{{ request('max_price') }}" placeholder="10,000,000 VNĐ">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-users me-2"></i>Số người</label>
                        <input type="number" name="capacity" class="form-control"  min="1" max="10"
                               value="{{ request('capacity') }}" placeholder="Số người" min="1">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-view-room">
                        <i class="fas fa-search me-2"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-redo me-2"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Info -->
    @if($rooms->total() > 0)
        <div class="results-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <strong>Tìm thấy {{ $rooms->total() }} phòng</strong>
                </div>
                <small class="text-muted">Hiển thị {{ $rooms->firstItem() }} - {{ $rooms->lastItem() }} trong {{ $rooms->total() }} kết quả</small>
            </div>
        </div>
    @endif

    <!-- Room List -->
    <div class="row g-4">
        @forelse($rooms as $room)
            <div class="col-lg-4 col-md-6">
                <div class="card room-card">
                    <div class="position-relative">
                        @php
                            $primaryImg = $room->primaryImage ?? $room->images->first();
                            $displayImage = $primaryImg ? $primaryImg->image_path : ($room->image ?? null);
                        @endphp
                        @if($displayImage)
                            <img src="{{ asset('storage/' . $displayImage) }}" class="room-card-img" alt="Phòng {{ $room->room_number }}">
                        @else
                            <div class="room-card-placeholder text-white">
                                <i class="fas fa-bed fa-4x opacity-75"></i>
                            </div>
                        @endif
                        
                        <!-- Room Type Badge -->
                        <span class="room-type-badge">
                            <i class="fas fa-star text-warning me-1"></i>{{ $room->room_type }}
                        </span>
                        
                        <!-- Status Badge -->
                        @if($room->status == 'available')
                            <span class="room-status-badge bg-success text-white">
                                <i class="fas fa-check-circle me-1"></i>Còn trống
                            </span>
                        @else
                            <span class="room-status-badge bg-danger text-white">
                                <i class="fas fa-times-circle me-1"></i>Đã đặt
                            </span>
                        @endif
                    </div>
                    
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3">Phòng {{ $room->room_number }}</h5>
                        <p class="card-text text-muted small mb-3">{{ Str::limit($room->description, 90) }}</p>
                        
                        <!-- Amenities -->
                        <div class="mb-3">
                            <span class="amenity-icon">
                                <i class="fas fa-users me-1"></i> {{ $room->capacity }} người
                            </span>
                            @if($room->amenities && count($room->amenities) > 0)
                                <span class="amenity-icon">
                                    <i class="fas fa-concierge-bell me-1"></i> {{ count($room->amenities) }} tiện nghi
                                </span>
                            @endif
                        </div>
                        
                        <!-- Price Tag -->
                        <div class="price-tag d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75 d-block mb-1">Giá mỗi đêm</small>
                                <h4 class="mb-0 fw-bold">{{ number_format($room->price_per_night) }} ₫</h4>
                            </div>
                            <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-light btn-sm rounded-pill px-3">
                                Chi tiết <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">Không tìm thấy phòng phù hợp</h4>
                    <p class="text-muted mb-4">Vui lòng thử lại với bộ lọc khác hoặc xem tất cả phòng</p>
                    <a href="{{ route('rooms.index') }}" class="btn btn-view-room">
                        <i class="fas fa-redo me-2"></i> Xem tất cả phòng
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($rooms->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $rooms->links() }}
        </div>
    @endif
</div>
@endsection

