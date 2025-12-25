@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user"></i> Chi tiết khách hàng</h2>
    <div>
        <a href="{{ route('admin.employee.customers.edit', $customer->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="{{ route('admin.employee.customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Customer Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Tên khách hàng:</label>
                    <p class="fw-bold mb-0">{{ $customer->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email:</label>
                    <p class="mb-0">{{ $customer->email }}</p>
                </div>
                @if($customer->phone)
                <div class="mb-3">
                    <label class="text-muted small">Số điện thoại:</label>
                    <p class="mb-0">{{ $customer->phone }}</p>
                </div>
                @endif
                <div class="mb-3">
                    <label class="text-muted small">Ngày đăng ký:</label>
                    <p class="mb-0">{{ $customer->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thống kê</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Tổng số đặt phòng:</label>
                    <p class="fw-bold mb-0 fs-4">{{ $customer->bookings->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Bookings List Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Lịch sử đặt phòng</h5>
            </div>
            <div class="card-body">
                @if($customer->bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã đặt</th>
                                    <th>Số phòng</th>
                                    <th>Ngày nhận</th>
                                    <th>Ngày trả</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->bookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->room->room_number ?? '-' }}</td>
                                        <td>{{ $booking->check_in_date ? \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $booking->check_out_date ? \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-bold">{{ number_format($booking->total_price) }} VNĐ</td>
                                        <td>
                                            @if($booking->status == 'pending')
                                                <span class="badge bg-warning">Chờ xử lý</span>
                                            @elseif($booking->status == 'confirmed')
                                                <span class="badge bg-success">Đã xác nhận</span>
                                            @elseif($booking->status == 'checked_in')
                                                <span class="badge bg-info">Đã nhận phòng</span>
                                            @elseif($booking->status == 'checked_out')
                                                <span class="badge bg-secondary">Đã trả phòng</span>
                                            @else
                                                <span class="badge bg-danger">Đã hủy</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->payment && $booking->payment->payment_status == 'completed')
                                                <span class="badge bg-success">Đã thanh toán</span>
                                            @else
                                                <span class="badge bg-danger">Chưa thanh toán</span>
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
                        <p class="text-muted">Khách hàng chưa có đặt phòng nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

