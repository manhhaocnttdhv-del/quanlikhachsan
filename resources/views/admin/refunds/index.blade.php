@extends('layouts.admin')

@section('title', 'Quản lý Yêu cầu Hoàn tiền')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-money-bill-wave"></i> Quản lý Yêu cầu Hoàn tiền</h2>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Tổng yêu cầu</h6>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Chờ xử lý</h6>
                <h3>{{ $stats['pending'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Đã duyệt</h6>
                <h3>{{ $stats['approved'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Đã hoàn thành</h6>
                <h3>{{ $stats['completed'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.refunds.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="refund_method" class="form-select">
                    <option value="">-- Tất cả phương thức --</option>
                    <option value="bank_transfer" {{ request('refund_method') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                    <option value="qr_code" {{ request('refund_method') == 'qr_code' ? 'selected' : '' }}>QR Code</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
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
                        <th>Mã yêu cầu</th>
                        <th>Khách hàng</th>
                        <th>Mã đặt phòng</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refundRequests as $request)
                        <tr>
                            <td><strong>#{{ $request->id }}</strong></td>
                            <td>
                                {{ $request->user->name }}<br>
                                <small class="text-muted">{{ $request->user->email }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $request->booking_id) }}" class="text-primary">
                                    #{{ $request->booking_id }}
                                </a>
                            </td>
                            <td><strong>{{ number_format($request->refund_amount) }} VNĐ</strong></td>
                            <td>
                                @if($request->refund_method === 'bank_transfer')
                                    <span class="badge bg-info">
                                        <i class="fas fa-university"></i> Chuyển khoản
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-qrcode"></i> QR Code
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @elseif($request->status === 'approved')
                                    <span class="badge bg-info">Đã duyệt</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">Đã từ chối</span>
                                @else
                                    <span class="badge bg-success">Đã hoàn thành</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.refunds.show', $request->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Không có yêu cầu hoàn tiền nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $refundRequests->links() }}
        </div>
    </div>
</div>
@endsection

