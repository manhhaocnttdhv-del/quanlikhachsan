@extends('layouts.admin')

@section('title', 'Chi tiết báo cáo ca #' . $report->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt"></i> Chi tiết báo cáo ca #{{ $report->id }}</h2>
    <a href="{{ route('admin.shift-reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin báo cáo</h5>
                    @if($report->status == 'submitted')
                        <span class="badge bg-success fs-6">Đã gửi</span>
                    @else
                        <span class="badge bg-warning fs-6">Nháp</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Nhân viên</th>
                        <td>
                            <strong>{{ $report->admin->name }}</strong><br>
                            <small class="text-muted">{{ $report->admin->email }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Ngày báo cáo</th>
                        <td>{{ $report->report_date->format('d/m/Y') }}</td>
                    </tr>
                    @if($report->shift)
                    <tr>
                        <th>Ca làm việc</th>
                        <td>
                            {{ $report->shift->start_time }} - {{ $report->shift->end_time }}
                            <br>
                            <small class="text-muted">{{ $report->shift->shift_date->format('d/m/Y') }}</small>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>Ngày tạo</th>
                        <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Doanh thu theo phương thức</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tiền mặt:</label>
                        <p class="fw-bold fs-5">{{ number_format($report->cash_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Thẻ tín dụng:</label>
                        <p class="fw-bold fs-5">{{ number_format($report->card_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Chuyển khoản:</label>
                        <p class="fw-bold fs-5">{{ number_format($report->transfer_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Khác:</label>
                        <p class="fw-bold fs-5">{{ number_format($report->other_amount) }} VNĐ</p>
                    </div>
                    <div class="col-12">
                        <hr>
                        <label class="text-muted small">Tổng doanh thu:</label>
                        <p class="fw-bold fs-4 text-primary">{{ number_format($report->total_revenue) }} VNĐ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Thống kê checkout</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Tổng số checkout:</label>
                        <p class="fw-bold fs-5">{{ $report->total_checkouts }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Đã thanh toán:</label>
                        <p class="fw-bold fs-5 text-success">{{ $report->paid_checkouts }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Chưa thanh toán:</label>
                        <p class="fw-bold fs-5 text-danger">{{ $report->unpaid_checkouts }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($report->notes)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Ghi chú</h5>
            </div>
            <div class="card-body">
                <p>{{ $report->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

