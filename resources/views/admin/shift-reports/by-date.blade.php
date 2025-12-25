@extends('layouts.admin')

@section('title', 'Doanh thu theo ngày')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar"></i> Doanh thu theo ngày</h2>
    <div>
        <a href="{{ route('admin.shift-reports.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-list"></i> Danh sách báo cáo
        </a>
        <a href="{{ route('admin.shift-reports.by-employee') }}" class="btn btn-info">
            <i class="fas fa-users"></i> Theo nhân viên
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.shift-reports.by-date') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from', date('Y-m-d', strtotime('-30 days'))) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to', date('Y-m-d')) }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.shift-reports.by-date') }}" class="btn btn-secondary">
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
                        <th>Số báo cáo</th>
                        <th>Tổng doanh thu</th>
                        <th>Tiền mặt</th>
                        <th>Thẻ</th>
                        <th>Chuyển khoản</th>
                        <th>Tổng checkout</th>
                        <th>Đã thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dateStats as $stat)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($stat['date'])->format('d/m/Y') }}</strong><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($stat['date'])->locale('vi')->dayName }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $stat['report_count'] }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">{{ number_format($stat['total_revenue']) }} VNĐ</strong>
                            </td>
                            <td>{{ number_format($stat['total_cash']) }} VNĐ</td>
                            <td>{{ number_format($stat['total_card']) }} VNĐ</td>
                            <td>{{ number_format($stat['total_transfer']) }} VNĐ</td>
                            <td>{{ $stat['total_checkouts'] }}</td>
                            <td>
                                <span class="badge bg-success">{{ $stat['total_paid_checkouts'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Không có dữ liệu</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($dateStats) > 0)
                <tfoot>
                    <tr class="table-primary">
                        <th>TỔNG CỘNG</th>
                        <th>{{ collect($dateStats)->sum('report_count') }}</th>
                        <th>{{ number_format(collect($dateStats)->sum('total_revenue')) }} VNĐ</th>
                        <th>{{ number_format(collect($dateStats)->sum('total_cash')) }} VNĐ</th>
                        <th>{{ number_format(collect($dateStats)->sum('total_card')) }} VNĐ</th>
                        <th>{{ number_format(collect($dateStats)->sum('total_transfer')) }} VNĐ</th>
                        <th>{{ collect($dateStats)->sum('total_checkouts') }}</th>
                        <th>{{ collect($dateStats)->sum('total_paid_checkouts') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

