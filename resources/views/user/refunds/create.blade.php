@extends('layouts.app')

@section('title', 'Yêu cầu hoàn tiền')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.bookings.index') }}">Đặt phòng của tôi</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.payments.show', $payment->id) }}">Thanh toán #{{ $payment->id }}</a></li>
            <li class="breadcrumb-item active">Yêu cầu hoàn tiền</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave"></i> Yêu cầu hoàn tiền
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Thông tin thanh toán:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Số tiền: <strong>{{ number_format($payment->amount) }} VNĐ</strong></li>
                            <li>Mã thanh toán: <strong>#{{ $payment->id }}</strong></li>
                            <li>Mã đặt phòng: <strong>#{{ $payment->booking->id }}</strong></li>
                        </ul>
                    </div>

                    @if($payment->booking->status !== 'cancelled')
                        @php
                            $checkInDate = \Carbon\Carbon::parse($payment->booking->check_in_date)->startOfDay();
                            $today = \Carbon\Carbon::today()->startOfDay();
                            $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
                            $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
                            $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
                            
                            $cancellationFee = 0;
                            $refundAmount = $payment->amount;
                            if ($daysUntilCheckIn < $cancellationDaysForFullRefund && $daysUntilCheckIn >= 1) {
                                $cancellationFee = $payment->amount * ($cancellationFeePercentage / 100);
                                $refundAmount = $payment->amount - $cancellationFee;
                            }
                        @endphp
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Lưu ý quan trọng:</strong>
                            <p class="mb-2 mt-2">
                                Sau khi nhập thông tin hoàn tiền và gửi yêu cầu, đặt phòng sẽ được <strong>tự động hủy</strong>.
                            </p>
                            @if($cancellationFee > 0)
                                <p class="mb-0">
                                    <strong>Phí hủy phòng:</strong> {{ number_format($cancellationFee) }} VNĐ ({{ $cancellationFeePercentage }}%)
                                    <br>
                                    <strong>Số tiền được hoàn:</strong> {{ number_format($refundAmount) }} VNĐ
                                </p>
                            @else
                                <p class="mb-0">
                                    <strong>Số tiền được hoàn:</strong> {{ number_format($refundAmount) }} VNĐ (100%)
                                </p>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('user.refunds.store', $payment->id) }}" method="POST" id="refundForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Phương thức hoàn tiền *</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="refund_method" id="bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university"></i> Chuyển khoản ngân hàng
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refund_method" id="qr_code" value="qr_code">
                                <label class="form-check-label" for="qr_code">
                                    <i class="fas fa-qrcode"></i> Mã QR Code
                                </label>
                            </div>
                        </div>

                        {{-- Thông tin chuyển khoản --}}
                        <div id="bankTransferFields">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Tên ngân hàng *</label>
                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name" 
                                       value="{{ old('bank_name') }}" 
                                       placeholder="Ví dụ: Vietcombank, BIDV, Techcombank...">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="account_number" class="form-label">Số tài khoản *</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" name="account_number" 
                                       value="{{ old('account_number') }}" 
                                       placeholder="Nhập số tài khoản ngân hàng">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="account_holder_name" class="form-label">Tên chủ tài khoản *</label>
                                <input type="text" class="form-control @error('account_holder_name') is-invalid @enderror" 
                                       id="account_holder_name" name="account_holder_name" 
                                       value="{{ old('account_holder_name') }}" 
                                       placeholder="Nhập tên chủ tài khoản">
                                @error('account_holder_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Mã QR Code --}}
                        <div id="qrCodeFields" style="display: none;">
                            <div class="mb-3">
                                <label for="qr_code" class="form-label">Mã QR Code *</label>
                                <textarea class="form-control @error('qr_code') is-invalid @enderror" 
                                          id="qr_code" name="qr_code" rows="4" 
                                          placeholder="Dán mã QR Code hoặc link QR Code của bạn">{{ old('qr_code') }}</textarea>
                                <small class="text-muted">Vui lòng dán mã QR Code hoặc link QR Code để nhận hoàn tiền</small>
                                @error('qr_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Nhập ghi chú nếu có">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Lưu ý:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Vui lòng kiểm tra kỹ thông tin tài khoản/QR Code trước khi gửi yêu cầu</li>
                                <li>Admin sẽ xử lý yêu cầu hoàn tiền trong thời gian sớm nhất</li>
                                <li>Thời gian hoàn tiền: 3-5 ngày làm việc</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('user.payments.show', $payment->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi yêu cầu hoàn tiền
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankTransferRadio = document.getElementById('bank_transfer');
    const qrCodeRadio = document.getElementById('qr_code');
    const bankTransferFields = document.getElementById('bankTransferFields');
    const qrCodeFields = document.getElementById('qrCodeFields');

    function toggleFields() {
        if (bankTransferRadio.checked) {
            bankTransferFields.style.display = 'block';
            qrCodeFields.style.display = 'none';
            // Required fields
            document.getElementById('bank_name').required = true;
            document.getElementById('account_number').required = true;
            document.getElementById('account_holder_name').required = true;
            document.getElementById('qr_code').required = false;
        } else {
            bankTransferFields.style.display = 'none';
            qrCodeFields.style.display = 'block';
            // Required fields
            document.getElementById('bank_name').required = false;
            document.getElementById('account_number').required = false;
            document.getElementById('account_holder_name').required = false;
            document.getElementById('qr_code').required = true;
        }
    }

    bankTransferRadio.addEventListener('change', toggleFields);
    qrCodeRadio.addEventListener('change', toggleFields);
    
    // Initialize on page load
    toggleFields();
});
</script>
@endpush
@endsection
