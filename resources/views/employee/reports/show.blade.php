@extends('layouts.admin')

@section('title', 'Chi tiết báo cáo ca của tôi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt"></i> Chi tiết báo cáo ca của tôi #{{ $report->id }}</h2>
    <div>
        @if($report->status == 'draft')
            <a href="{{ route('admin.employee.reports.edit', $report->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <form action="{{ route('admin.employee.reports.destroy', $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa báo cáo này?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </form>
        @endif
        <a href="{{ route('admin.employee.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày báo cáo:</label>
                        <p class="fw-bold">{{ $report->report_date->format('d/m/Y') }}</p>
                    </div>
                    @if($report->shift)
                    <div class="col-md-6">
                        <label class="text-muted small">Ca làm việc:</label>
                        <p class="fw-bold">{{ $report->shift->start_time }} - {{ $report->shift->end_time }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Doanh thu</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Tiền mặt:</label>
                        <p class="fw-bold">{{ number_format($report->cash_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Thẻ/Chuyển khoản:</label>
                        <p class="fw-bold">{{ number_format($report->card_amount + $report->transfer_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Tổng doanh thu:</label>
                        <p class="fw-bold fs-5 text-primary">{{ number_format($report->total_revenue) }} VNĐ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Checkout</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Tổng số:</label>
                        <p class="fw-bold">{{ $report->total_checkouts }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Đã thanh toán:</label>
                        <p class="fw-bold text-success">{{ $report->paid_checkouts }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Chưa thanh toán:</label>
                        <p class="fw-bold text-danger">{{ $report->unpaid_checkouts }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($report->notes)
        <div class="card">
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

