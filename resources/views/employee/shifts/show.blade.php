@extends('layouts.admin')

@section('title', 'Chi tiết ca làm việc')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt"></i> Chi tiết ca làm việc</h2>
    <a href="{{ route('admin.employee.shifts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Ngày làm việc</h6>
                <h4 class="mb-0">{{ \Carbon\Carbon::parse($shift->shift_date)->format('d/m/Y') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Ca làm việc</h6>
                <h4 class="mb-0">{{ $shift->getShiftTypeName() }}</h4>
                <small class="text-muted">{{ $shift->start_time }} - {{ $shift->end_time }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Trạng thái</h6>
                @if($shift->status == 'scheduled')
                    <span class="badge bg-info fs-6">Đã lên lịch</span>
                @elseif($shift->status == 'active')
                    <span class="badge bg-success fs-6">Đang làm</span>
                @elseif($shift->status == 'completed')
                    <span class="badge bg-secondary fs-6">Hoàn thành</span>
                @else
                    <span class="badge bg-danger fs-6">Đã hủy</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Tổng booking</h6>
                <h3 class="mb-0">{{ $totalBookings }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Đã check-in</h6>
                <h3 class="mb-0">{{ $checkedInBookings }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Đã check-out</h6>
                <h3 class="mb-0">{{ $checkedOutBookings }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h6 class="mb-2">Doanh thu</h6>
                <h3 class="mb-0">{{ number_format($revenue) }} VNĐ</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Danh sách booking trong ca</h5>
    </div>
    <div class="card-body">
        @if($shift->bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã đặt</th>
                            <th>Khách hàng</th>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Checkout</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shift->bookings as $booking)
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>{{ $booking->user->name }}</td>
                                <td>{{ $booking->room->room_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</td>
                                <td><strong>{{ number_format($booking->total_price) }} VNĐ</strong></td>
                                <td>
                                    @if($booking->payment && $booking->payment->payment_status == 'completed')
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    @else
                                        <span class="badge bg-danger">Chưa thanh toán</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status == 'confirmed')
                                        <span class="badge bg-success">Đã xác nhận</span>
                                    @elseif($booking->status == 'checked_in')
                                        <span class="badge bg-info">Đã nhận phòng</span>
                                    @elseif($booking->status == 'checked_out')
                                        <span class="badge bg-secondary">Đã trả phòng</span>
                                    @else
                                        <span class="badge bg-warning">Chờ xử lý</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có booking nào trong ca này</p>
            </div>
        @endif
    </div>
</div>
@endsection

