@extends('layouts.admin')

@section('title', 'Kết quả tìm kiếm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-search"></i> Kết quả tìm kiếm: "{{ $query }}"</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

@if($bookings->count() > 0 || $customers->count() > 0)
    @if($bookings->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Booking ({{ $bookings->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã đặt</th>
                            <th>Khách hàng</th>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Checkout</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>{{ $booking->user->name }}</td>
                                <td>{{ $booking->room->room_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if($booking->status == 'confirmed')
                                        <span class="badge bg-success">Đã xác nhận</span>
                                    @elseif($booking->status == 'checked_in')
                                        <span class="badge bg-info">Đã nhận phòng</span>
                                    @else
                                        <span class="badge bg-warning">Chờ xử lý</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($customers->count() > 0)
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Khách hàng ({{ $customers->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.employee.customers.show', $customer->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <p class="text-muted">Không tìm thấy kết quả nào cho "{{ $query }}"</p>
        </div>
    </div>
@endif
@endsection

