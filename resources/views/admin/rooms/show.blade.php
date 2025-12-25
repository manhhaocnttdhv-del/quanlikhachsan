@extends('layouts.admin')

@section('title', 'Chi tiết phòng ' . $room->room_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bed"></i> Chi tiết phòng {{ $room->room_number }}</h2>
    <div>
        <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <!-- Gallery ảnh -->
            @if($room->images->count() > 0 || $room->image)
                <div id="roomImageCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @if($room->image)
                            <div class="carousel-item active">
                                <img src="{{ asset('storage/' . $room->image) }}" class="d-block w-100" 
                                     style="height: 400px; object-fit: cover;" alt="{{ $room->room_number }}">
                            </div>
                        @endif
                        @foreach($room->images as $index => $image)
                            <div class="carousel-item {{ !$room->image && $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="d-block w-100" 
                                     style="height: 400px; object-fit: cover;" alt="{{ $room->room_number }}">
                                @if($image->is_primary)
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-success">Ảnh chính</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if(($room->images->count() > 0 && $room->image) || $room->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="bg-secondary text-white text-center" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bed fa-5x"></i>
                </div>
            @endif
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số phòng:</label>
                        <h4>{{ $room->room_number }}</h4>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Trạng thái:</label>
                        <div>
                            @if($room->status == 'available')
                                <span class="badge bg-success fs-6">Trống</span>
                            @elseif($room->status == 'occupied')
                                <span class="badge bg-danger fs-6">Đã đặt</span>
                            @else
                                <span class="badge bg-warning fs-6">Bảo trì</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Loại phòng:</label>
                        <p class="fw-bold">{{ $room->room_type }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Sức chứa:</label>
                        <p class="fw-bold"><i class="fas fa-users"></i> {{ $room->capacity }} người</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Giá/đêm:</label>
                        <p class="fw-bold text-primary">{{ number_format($room->price_per_night) }} VNĐ</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Mô tả:</label>
                    <p>{{ $room->description }}</p>
                </div>

                @if($room->amenities)
                    <div class="mb-3">
                        <label class="text-muted small">Tiện nghi:</label>
                        <div class="row">
                            @foreach($room->amenities as $amenity)
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check-circle text-success"></i> {{ $amenity }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Lịch sử đặt phòng</h5>
            </div>
            <div class="card-body">
                @if($room->bookings->count() > 0)
                    <p class="mb-2">Tổng số lượt đặt: <strong>{{ $room->bookings->count() }}</strong></p>
                    <p class="mb-2">Đang hoạt động: <strong>{{ $room->bookings->whereIn('status', ['confirmed', 'checked_in'])->count() }}</strong></p>
                    <p class="mb-0">Đã hoàn thành: <strong>{{ $room->bookings->where('status', 'checked_out')->count() }}</strong></p>
                @else
                    <p class="text-muted mb-0">Chưa có đặt phòng nào</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khác</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <small class="text-muted">Ngày tạo:</small><br>
                    {{ $room->created_at->format('d/m/Y H:i') }}
                </p>
                <p class="mb-0">
                    <small class="text-muted">Cập nhật lần cuối:</small><br>
                    {{ $room->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

