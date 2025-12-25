@extends('layouts.admin')

@section('title', 'Quản lý Thanh toán')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-dollar-sign"></i> Quản lý Thanh toán</h2>
</div>

<!-- Revenue Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Tổng doanh thu</h6>
                <h3>{{ number_format($totalRevenue) }} VNĐ</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Doanh thu tháng này</h6>
                <h3>{{ number_format($monthlyRevenue) }} VNĐ</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <select name="payment_status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-5">
                <select name="payment_method" class="form-select">
                    <option value="">-- Tất cả phương thức --</option>
                    <option value="bank_transfer_qr" {{ request('payment_method') == 'bank_transfer_qr' ? 'selected' : '' }}>QR Chuyển khoản</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Lọc
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
                        <th>ID</th>
                        <th>Đặt phòng</th>
                        <th>Khách hàng</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày TT</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>#{{ $payment->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $payment->booking->id) }}">
                                    #{{ $payment->booking->id }}
                                </a>
                            </td>
                            <td>{{ $payment->booking->user->name }}</td>
                            <td><strong>{{ number_format($payment->amount) }} VNĐ</strong></td>
                            <td>
                                @if($payment->payment_method == 'cash')
                                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                @elseif($payment->payment_method == 'credit_card')
                                    <i class="fas fa-credit-card"></i> Thẻ
                                @elseif($payment->payment_method == 'bank_transfer')
                                    <i class="fas fa-university"></i> Chuyển khoản
                                @elseif($payment->payment_method == 'bank_transfer_qr')
                                    <i class="fas fa-qrcode"></i> QR Chuyển khoản
                                @elseif($payment->payment_method == 'momo')
                                    <i class="fas fa-mobile-alt"></i> MoMo
                                @elseif($payment->payment_method == 'vnpay')
                                    <i class="fas fa-wallet"></i> VNPay
                                @else
                                    {{ $payment->payment_method }}
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_status == 'completed')
                                    <span class="badge bg-success">Hoàn thành</span>
                                @elseif($payment->payment_status == 'pending')
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @elseif($payment->payment_status == 'failed')
                                    <span class="badge bg-danger">Thất bại</span>
                                @else
                                    <span class="badge bg-secondary">Đã hoàn tiền</span>
                                @endif
                            </td>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bạn có chắc muốn xóa thanh toán này?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có thanh toán nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection

