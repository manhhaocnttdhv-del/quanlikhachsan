@extends('layouts.admin')

@section('title', 'Quản lý Ca làm việc')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt"></i> Quản lý Ca làm việc</h2>
    <div>
        <a href="{{ route('admin.shifts.createMonthly') }}" class="btn btn-success me-2">
            <i class="fas fa-calendar-check"></i> Phân công theo tháng
        </a>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Phân công ca mới
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Nhân viên</label>
                <select name="admin_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('admin_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->role == 'employee' ? 'Nhân viên' : 'Quản lý' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ngày</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Đã lên lịch</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang làm</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
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
                        <th>Nhân viên</th>
                        <th>Ngày</th>
                        <th>Ca làm việc</th>
                        <th>Giờ</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>#{{ $shift->id }}</td>
                            <td>
                                <strong>{{ $shift->admin->name }}</strong><br>
                                <small class="text-muted">{{ $shift->admin->email }}</small>
                            </td>
                            <td>{{ $shift->shift_date->format('d/m/Y') }}</td>
                            <td>
                                @if($shift->shift_type)
                                    <span class="badge bg-primary">{{ $shift->getShiftTypeName() }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                            <td>
                                @if($shift->status == 'scheduled')
                                    <span class="badge bg-info">Đã lên lịch</span>
                                @elseif($shift->status == 'active')
                                    <span class="badge bg-success">Đang làm</span>
                                @elseif($shift->status == 'completed')
                                    <span class="badge bg-secondary">Hoàn thành</span>
                                @else
                                    <span class="badge bg-danger">Đã hủy</span>
                                @endif
                            </td>
                            <td>
                                @if($shift->notes)
                                    <span title="{{ $shift->notes }}">{{ Str::limit($shift->notes, 30) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.shifts.show', $shift->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bạn có chắc muốn xóa ca này?')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có ca làm việc nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $shifts->links() }}
        </div>
    </div>
</div>
@endsection

