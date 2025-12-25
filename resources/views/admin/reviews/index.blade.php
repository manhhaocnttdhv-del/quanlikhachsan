@extends('layouts.admin')

@section('title', 'Quản lý Đánh giá')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-star text-warning"></i> Quản lý Đánh giá</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm kiếm theo tên khách hàng, email...">
            </div>
            <div class="col-md-3">
                <input type="text" name="room_search" class="form-control" 
                       value="{{ request('room_search') }}" 
                       placeholder="Tìm kiếm theo số phòng, loại phòng...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Đánh giá</th>
                        <th>Nhận xét</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>#{{ $review->id }}</td>
                            <td>
                                <strong>{{ $review->user->name }}</strong><br>
                                <small class="text-muted">{{ $review->user->email }}</small>
                            </td>
                            <td>
                                <strong>{{ $review->room->room_number }}</strong><br>
                                <small class="text-muted">{{ $review->room->room_type }}</small>
                            </td>
                            <td>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-1">{{ $review->rating }}/5</span>
                                </div>
                            </td>
                            <td>
                                @if($review->comment)
                                    <p class="mb-0 small">{{ Str::limit($review->comment, 50) }}</p>
                                @else
                                    <span class="text-muted small">Không có nhận xét</span>
                                @endif
                            </td>
                            <td>
                                @if($review->status == 'pending')
                                    <span class="badge bg-warning">Chờ duyệt</span>
                                @elseif($review->status == 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                @else
                                    <span class="badge bg-danger">Đã từ chối</span>
                                @endif
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($review->status == 'pending')
                                    <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có đánh giá nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection

