@extends('layouts.app')

@section('title', 'Đánh giá của tôi')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Đánh giá của tôi</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-star text-warning"></i> Đánh giá của tôi</h2>
    </div>

    @if($reviews->count() > 0)
        <div class="row">
            @foreach($reviews as $review)
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <a href="{{ route('rooms.show', $review->room_id) }}" class="text-decoration-none">
                                            Phòng {{ $review->room->room_number }}
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-0">{{ $review->room->room_type }}</p>
                                </div>
                                <div>
                                    @if($review->status == 'approved')
                                        <span class="badge bg-success">Đã duyệt</span>
                                    @elseif($review->status == 'pending')
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    @else
                                        <span class="badge bg-danger">Đã từ chối</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-2">{{ $review->rating }}/5</span>
                            </div>

                            @if($review->comment)
                                <p class="mb-3">{{ $review->comment }}</p>
                            @endif

                            @if($review->booking)
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar"></i> Đặt phòng #{{ $review->booking->id }}
                                </p>
                            @endif

                            <p class="text-muted small mb-0">
                                <i class="fas fa-clock"></i> {{ $review->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-star fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Bạn chưa có đánh giá nào</h5>
                <p class="text-muted">Hãy đánh giá phòng sau khi sử dụng dịch vụ!</p>
                <a href="{{ route('rooms.index') }}" class="btn btn-primary">
                    <i class="fas fa-search"></i> Xem phòng
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

