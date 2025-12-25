@extends('layouts.admin')

@section('title', 'Chi tiết Đánh giá #' . $review->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-star text-warning"></i> Chi tiết Đánh giá #{{ $review->id }}</h2>
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đánh giá</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small">Đánh giá sao:</label>
                    <div class="mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star fa-2x {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                        @endfor
                        <span class="ms-3 fs-5">{{ $review->rating }}/5</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Nhận xét:</label>
                    <div class="mt-2 p-3 bg-light rounded">
                        @if($review->comment)
                            <p class="mb-0">{{ $review->comment }}</p>
                        @else
                            <p class="text-muted mb-0">Không có nhận xét</p>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Trạng thái:</label>
                    <div class="mt-2">
                        @if($review->status == 'pending')
                            <span class="badge bg-warning fs-6">Chờ duyệt</span>
                        @elseif($review->status == 'approved')
                            <span class="badge bg-success fs-6">Đã duyệt</span>
                        @else
                            <span class="badge bg-danger fs-6">Đã từ chối</span>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Ngày tạo:</label>
                    <p class="mb-0">{{ $review->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Cập nhật lần cuối:</label>
                    <p class="mb-0">{{ $review->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>

                @if($review->status == 'pending')
                    <div class="d-flex gap-2 mt-4">
                        <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Duyệt đánh giá
                            </button>
                        </form>
                        <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Bạn có chắc muốn từ chối đánh giá này?')">
                                <i class="fas fa-times"></i> Từ chối đánh giá
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Tên:</label>
                    <p class="mb-0 fw-bold">{{ $review->user->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email:</label>
                    <p class="mb-0">{{ $review->user->email }}</p>
                </div>
                @if($review->user->phone)
                    <div class="mb-3">
                        <label class="text-muted small">Số điện thoại:</label>
                        <p class="mb-0">{{ $review->user->phone }}</p>
                    </div>
                @endif
                <a href="{{ route('admin.customers.show', $review->user->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Xem chi tiết khách hàng
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin phòng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Số phòng:</label>
                    <p class="mb-0 fw-bold">{{ $review->room->room_number }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Loại phòng:</label>
                    <p class="mb-0">{{ $review->room->room_type }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Giá:</label>
                    <p class="mb-0">{{ number_format($review->room->price_per_night) }} VNĐ/đêm</p>
                </div>
                <a href="{{ route('admin.rooms.show', $review->room->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Xem chi tiết phòng
                </a>
            </div>
        </div>

        @if($review->booking)
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Thông tin đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Mã đặt phòng:</label>
                        <p class="mb-0 fw-bold">#{{ $review->booking->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Ngày nhận:</label>
                        <p class="mb-0">{{ $review->booking->check_in_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Ngày trả:</label>
                        <p class="mb-0">{{ $review->booking->check_out_date->format('d/m/Y') }}</p>
                    </div>
                    <a href="{{ route('admin.bookings.show', $review->booking->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Xem chi tiết đặt phòng
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

