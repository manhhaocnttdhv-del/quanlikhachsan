@extends('layouts.admin')

@section('title', 'Doanh thu theo ca')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line"></i> Doanh thu theo ca</h2>
    <div>
        <a href="{{ route('admin.shift-reports.by-employee') }}" class="btn btn-info me-2">
            <i class="fas fa-users"></i> Theo nhân viên
        </a>
        <a href="{{ route('admin.shift-reports.by-date') }}" class="btn btn-success">
            <i class="fas fa-calendar"></i> Theo ngày
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng doanh thu</h6>
                        <h3 class="mb-0 text-primary">{{ number_format($totalRevenue) }} VNĐ</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tiền mặt</h6>
                        <h3 class="mb-0 text-success">{{ number_format($totalCash) }} VNĐ</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Thẻ/Chuyển khoản</h6>
                        <h3 class="mb-0 text-info">{{ number_format($totalCard + $totalTransfer) }} VNĐ</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-credit-card fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng checkout</h6>
                        <h3 class="mb-0">{{ $totalCheckouts }}</h3>
                    </div>
                    <div class="text-secondary">
                        <i class="fas fa-door-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Lọc theo thời gian</h5>
    </div>
    <div class="card-body">
        @if($errors->has('date_to'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first('date_to') }}
            </div>
        @endif
        <form method="GET" action="{{ route('admin.shift-reports.index') }}" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Nhân viên</label>
                <select name="admin_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('admin_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" id="dateFrom" class="form-control" value="{{ request('date_from', $dateFrom ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" id="dateTo" class="form-control" value="{{ request('date_to', $dateTo ?? '') }}" required>
                <div class="invalid-feedback" id="dateToError" style="display: none;">
                    Đến ngày không được nhỏ hơn từ ngày
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Đã gửi</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.shift-reports.index') }}" class="btn btn-secondary">
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
                        <th>Ngày báo cáo</th>
                        <th>Nhân viên</th>
                        <th>Ca làm việc</th>
                        <th>Tổng doanh thu</th>
                        <th>Tiền mặt</th>
                        <th>Thẻ/Chuyển khoản</th>
                        <th>Số checkout</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $report->admin->name }}</strong><br>
                                <small class="text-muted">{{ $report->admin->email }}</small>
                            </td>
                            <td>
                                @if($report->shift)
                                    {{ $report->shift->start_time }} - {{ $report->shift->end_time }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($report->total_revenue) }} VNĐ</strong></td>
                            <td>{{ number_format($report->cash_amount) }} VNĐ</td>
                            <td>{{ number_format($report->card_amount + $report->transfer_amount) }} VNĐ</td>
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
                                <a href="{{ route('admin.shift-reports.show', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const filterForm = document.getElementById('filterForm');
    const dateToError = document.getElementById('dateToError');
    
    // Validation khi submit form
    filterForm.addEventListener('submit', function(e) {
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        
        if (dateFrom && dateTo && dateTo < dateFrom) {
            e.preventDefault();
            dateToInput.classList.add('is-invalid');
            dateToError.style.display = 'block';
            dateToInput.focus();
            return false;
        } else {
            dateToInput.classList.remove('is-invalid');
            dateToError.style.display = 'none';
        }
    });
    
    // Validation real-time khi thay đổi
    dateToInput.addEventListener('change', function() {
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        
        if (dateFrom && dateTo && dateTo < dateFrom) {
            dateToInput.classList.add('is-invalid');
            dateToError.style.display = 'block';
        } else {
            dateToInput.classList.remove('is-invalid');
            dateToError.style.display = 'none';
        }
    });
    
    dateFromInput.addEventListener('change', function() {
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        
        // Tự động set min cho dateTo
        if (dateFrom) {
            dateToInput.setAttribute('min', dateFrom);
        }
        
        // Validate lại nếu dateTo đã có giá trị
        if (dateTo && dateTo < dateFrom) {
            dateToInput.classList.add('is-invalid');
            dateToError.style.display = 'block';
        } else {
            dateToInput.classList.remove('is-invalid');
            dateToError.style.display = 'none';
        }
    });
    
    // Set min cho dateTo khi load trang
    if (dateFromInput.value) {
        dateToInput.setAttribute('min', dateFromInput.value);
    }
});
</script>
@endsection

