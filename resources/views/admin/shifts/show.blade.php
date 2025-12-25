@extends('layouts.admin')

@section('title', 'Chi tiết ca làm việc')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Chi tiết ca làm việc #{{ $shift->id }}</h2>
    <div>
        <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Chỉnh sửa
        </a>
        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin ca làm việc</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Nhân viên</th>
                        <td>
                            <strong>{{ $shift->admin->name }}</strong><br>
                            <small class="text-muted">{{ $shift->admin->email }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Ngày làm việc</th>
                        <td>{{ $shift->shift_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Ca làm việc</th>
                        <td>
                            @if($shift->shift_type)
                                <span class="badge bg-primary fs-6">{{ $shift->getShiftTypeName() }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Giờ làm việc</th>
                        <td>{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
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
                    </tr>
                    @if($shift->notes)
                    <tr>
                        <th>Ghi chú</th>
                        <td>{{ $shift->notes }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Ngày tạo</th>
                        <td>{{ $shift->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Cập nhật lần cuối</th>
                        <td>{{ $shift->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

