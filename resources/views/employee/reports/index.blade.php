@extends('layouts.admin')

@section('title', 'Báo cáo ca của tôi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt"></i> Báo cáo ca của tôi</h2>
    <a href="{{ route('admin.employee.reports.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tạo báo cáo mới
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.reports.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Ngày</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Đã gửi</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.employee.reports.index') }}" class="btn btn-secondary">
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
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Tổng doanh thu</th>
                        <th>Checkout</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('d/m/Y') }}</td>
                            <td>
                                @if($report->shift)
                                    <small>{{ $report->shift->start_time }} - {{ $report->shift->end_time }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($report->total_revenue) }} VNĐ</strong></td>
                            <td>
                                <span class="badge bg-info">{{ $report->paid_checkouts }}/{{ $report->total_checkouts }}</span>
                            </td>
                            <td>
                                @if($report->status == 'submitted')
                                    <span class="badge bg-success">Đã gửi</span>
                                @else
                                    <span class="badge bg-warning">Nháp</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.employee.reports.show', $report->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($report->status == 'draft')
                                        <a href="{{ route('admin.employee.reports.edit', $report->id) }}" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.employee.reports.destroy', $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa báo cáo này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có báo cáo nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $reports->links() }}
        </div>
    </div>
</div>
@endsection

