@extends('layouts.admin')

@section('title', 'Hóa đơn #' . $booking->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> Hóa đơn #{{ $booking->id }}</h2>
    <div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> In hóa đơn
        </button>
        <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="card" id="invoice-content">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="text-primary">HÓA ĐƠN</h4>
                <p class="mb-0"><strong>Mã hóa đơn:</strong> #{{ $booking->id }}</p>
                <p class="mb-0"><strong>Ngày xuất:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Thông tin khách hàng</h5>
                <p class="mb-1"><strong>Tên:</strong> {{ $booking->user->name }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $booking->user->email }}</p>
                @if($booking->user->phone)
                <p class="mb-0"><strong>SĐT:</strong> {{ $booking->user->phone }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <h5>Thông tin đặt phòng</h5>
                <p class="mb-1"><strong>Mã đặt phòng:</strong> #{{ $booking->id }}</p>
                <p class="mb-1"><strong>Phòng:</strong> {{ $booking->room->room_number }} - {{ $booking->room->room_type }}</p>
                <p class="mb-1"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} {{ $booking->check_in_time ?? '14:00' }}</p>
                <p class="mb-0"><strong>Checkout:</strong> {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }} {{ $booking->check_out_time ?? '12:00' }}</p>
            </div>
        </div>

        <hr>

        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Mô tả</th>
                        <th class="text-end">Số lượng</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Tính giá phòng gốc (trừ đi phí phát sinh)
                        $additionalChargesTotal = $booking->additionalCharges ? $booking->additionalCharges->sum('total_price') : 0;
                        $originalRoomPrice = $booking->total_price - $additionalChargesTotal;
                        $nights = \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date));
                        $pricePerNight = $nights > 0 ? ($originalRoomPrice / $nights) : $booking->room->price_per_night;
                    @endphp
                    <tr>
                        <td>
                            Phòng {{ $booking->room->room_number }} - {{ $booking->room->room_type }}
                            <br>
                            <small class="text-muted">
                                Từ {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} 
                                đến {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}
                            </small>
                        </td>
                        <td class="text-end">
                            {{ $nights }} đêm
                        </td>
                        <td class="text-end">{{ number_format($pricePerNight) }} VNĐ</td>
                        <td class="text-end"><strong>{{ number_format($originalRoomPrice) }} VNĐ</strong></td>
                    </tr>
                    @if($booking->additionalCharges && $booking->additionalCharges->count() > 0)
                        @foreach($booking->additionalCharges as $charge)
                        <tr>
                            <td>
                                {{ $charge->service_name }}
                                @if($charge->notes)
                                    <br><small class="text-muted">{{ $charge->notes }}</small>
                                @endif
                            </td>
                            <td class="text-end">{{ $charge->quantity }}</td>
                            <td class="text-end">{{ number_format($charge->unit_price) }} VNĐ</td>
                            <td class="text-end"><strong>{{ number_format($charge->total_price) }} VNĐ</strong></td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Tổng cộng:</th>
                        <th class="text-end">{{ number_format($booking->total_price) }} VNĐ</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5>Phương thức thanh toán</h5>
                <p class="mb-0">
                    @if($booking->payment->payment_method == 'cash')
                        <strong>Tiền mặt</strong>
                    @elseif($booking->payment->payment_method == 'bank_transfer_qr')
                        <strong>Chuyển khoản QR</strong>
                    @else
                        <strong>{{ $booking->payment->payment_method }}</strong>
                    @endif
                </p>
                @if($booking->payment->transaction_id)
                <p class="mb-0"><strong>Mã giao dịch:</strong> {{ $booking->payment->transaction_id }}</p>
                @endif
                <p class="mb-0"><strong>Ngày thanh toán:</strong> {{ $booking->payment->payment_date->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0"><small class="text-muted">Cảm ơn quý khách đã sử dụng dịch vụ!</small></p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-content, #invoice-content * {
        visibility: visible;
    }
    #invoice-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn, .d-flex.justify-content-between {
        display: none !important;
    }
}
</style>
@endsection

