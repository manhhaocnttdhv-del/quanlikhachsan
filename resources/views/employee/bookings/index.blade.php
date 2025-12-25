@extends('layouts.admin')

@section('title', 'Danh sách Đặt phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Danh sách Đặt phòng</h2>
    <div class="btn-group">
        <a href="{{ route('admin.employee.bookings.index', ['status' => 'cancelled']) }}" 
           class="btn btn-outline-danger {{ request('status') == 'cancelled' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> Xem đã hủy
        </a>
        <a href="{{ route('admin.employee.bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Đặt phòng mới
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng số</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card pending h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Chờ xử lý</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card confirmed h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã xác nhận</h6>
                        <h3 class="mb-0 text-success">{{ $stats['confirmed'] }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card checked_in h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã nhận phòng</h6>
                        <h3 class="mb-0 text-info">{{ $stats['checked_in'] }}</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card completed h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Hoàn thành</h6>
                        <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3">
        <div class="card stats-card cancelled h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã hủy</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['cancelled'] }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.bookings.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="Tên, email, SĐT, số phòng, mã booking...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả (bao gồm đã hủy)</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Đã nhận phòng</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ngày check-in</label>
                <input type="date" name="check_in_date" class="form-control" 
                       value="{{ request('check_in_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Ngày check-out</label>
                <input type="date" name="check_out_date" class="form-control" 
                       value="{{ request('check_out_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td>
                            <strong>#{{ $booking->id }}</strong>
                            <br>
                            <small class="text-muted">{{ $booking->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <strong>{{ $booking->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $booking->user->email }}</small>
                            @if($booking->user->phone)
                            <br>
                            <small class="text-muted">{{ $booking->user->phone }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $booking->room->room_number }}</span>
                            <br>
                            <small class="text-muted">{{ $booking->room->room_type }}</small>
                        </td>
                        <td>
                            {{ $booking->check_in_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $booking->check_in_time ?? '14:00' }}</small>
                        </td>
                        <td>
                            {{ $booking->check_out_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $booking->check_out_time ?? '12:00' }}</small>
                        </td>
                        <td>
                            @if($booking->status == 'pending')
                                <span class="badge bg-warning">Chờ xử lý</span>
                            @elseif($booking->status == 'confirmed')
                                <span class="badge bg-success">Đã xác nhận</span>
                            @elseif($booking->status == 'checked_in')
                                <span class="badge bg-info">Đã nhận phòng</span>
                            @elseif($booking->status == 'completed')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-double"></i> Hoàn thành
                                </span>
                            @elseif($booking->status == 'cancelled')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle"></i> Đã hủy
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($booking->payment)
                                @if($booking->payment->payment_status == 'completed')
                                    <span class="badge bg-success mb-1 d-block">
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                    </span>
                                    @if($booking->payment->payment_method == 'bank_transfer_qr')
                                        <span class="badge bg-info">
                                            <i class="fas fa-qrcode"></i> QR
                                        </span>
                                    @elseif($booking->payment->payment_method == 'cash')
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ $booking->payment->payment_method }}
                                        </span>
                                    @endif
                                @elseif($booking->payment->payment_status == 'pending')
                                    <span class="badge bg-warning mb-1 d-block">Chờ thanh toán</span>
                                    @if($booking->payment->payment_method == 'bank_transfer_qr')
                                        <span class="badge bg-info">
                                            <i class="fas fa-qrcode"></i> QR
                                        </span>
                                        @if($booking->payment->receipt_image)
                                            <br><small class="text-info mt-1 d-block">
                                                <i class="fas fa-image"></i> Đã gửi biên lai
                                            </small>
                                        @endif
                                    @elseif($booking->payment->payment_method == 'cash')
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                        </span>
                                    @endif
                                @elseif($booking->payment->payment_status == 'failed')
                                    <span class="badge bg-danger mb-1 d-block">Thất bại</span>
                                    @if($booking->payment->payment_method == 'bank_transfer_qr')
                                        <span class="badge bg-info">
                                            <i class="fas fa-qrcode"></i> QR
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">{{ $booking->payment->payment_status }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">Chưa thanh toán</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($booking->total_price) }} VNĐ</strong>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($booking->status, ['confirmed', 'checked_in']))
                                    <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Checkout">
                                        <i class="fas fa-door-open"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Không có đặt phòng nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

