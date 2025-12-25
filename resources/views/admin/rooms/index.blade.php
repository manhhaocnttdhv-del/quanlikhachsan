@extends('layouts.admin')

@section('title', 'Quản lý Phòng - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bed"></i> Quản lý Phòng</h2>
    <div>
        <a href="{{ route('admin.rooms.import.form') }}" class="btn btn-info me-2">
            <i class="fas fa-file-import"></i> Import từ Excel/CSV
        </a>
        {{-- <a href="{{ route('admin.rooms.scrape.form') }}" class="btn btn-success me-2">
            <i class="fas fa-download"></i> Import từ Website
        </a> --}}
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm phòng mới
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.rooms.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Lọc theo trạng thái</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <option value="available" {{ $statusFilter == 'available' ? 'selected' : '' }}>Phòng trống</option>
                    <option value="occupied" {{ $statusFilter == 'occupied' ? 'selected' : '' }}>Phòng đã đặt</option>
                    <option value="maintenance" {{ $statusFilter == 'maintenance' ? 'selected' : '' }}>Phòng bảo trì</option>
                </select>
            </div>
            @if($statusFilter)
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Số phòng</th>
                        <th>Loại phòng</th>
                        <th>Sức chứa</th>
                        <th>Giá/đêm</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                        <tr>
                            <td><strong>{{ $room->room_number }}</strong></td>
                            <td>{{ $room->room_type }}</td>
                            <td><i class="fas fa-users"></i> {{ $room->capacity }} người</td>
                            <td>{{ number_format($room->price_per_night) }} VNĐ</td>
                            <td>
                                @if($room->status == 'available')
                                    <span class="badge bg-success">Trống</span>
                                @elseif($room->status == 'occupied')
                                    <span class="badge bg-danger">Đã đặt</span>
                                @else
                                    <span class="badge bg-warning">Bảo trì</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bạn có chắc muốn xóa phòng này?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Chưa có phòng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $rooms->links() }}
        </div>
    </div>
</div>
@endsection

