@extends('layouts.admin')

@section('title', 'Sửa thanh toán #' . $payment->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit"></i> Sửa thanh toán #{{ $payment->id }}</h2>
    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Cập nhật thông tin thanh toán</h5>
            </div>
            <div class="card-body">
                <!-- Thông tin thanh toán hiện tại (chỉ đọc) -->
                <div class="alert alert-info mb-4">
                    <h6 class="mb-3"><i class="fas fa-info-circle"></i> Thông tin thanh toán hiện tại:</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Mã thanh toán:</small>
                            <p class="fw-bold mb-0">#{{ $payment->id }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Số tiền:</small>
                            <p class="fw-bold mb-0 text-primary">{{ number_format($payment->amount) }} VNĐ</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Phương thức:</small>
                            <p class="fw-bold mb-0">
                                @if($payment->payment_method == 'cash')
                                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                @elseif($payment->payment_method == 'bank_transfer_qr')
                                    <i class="fas fa-qrcode"></i> QR Chuyển khoản
                                @elseif($payment->payment_method == 'vnpay')
                                    <i class="fas fa-wallet"></i> VNPay
                                @elseif($payment->payment_method == 'momo')
                                    <i class="fas fa-mobile-alt"></i> MoMo
                                @else
                                    {{ $payment->payment_method }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Trạng thái hiện tại:</small>
                            <p class="mb-0">
                                @if($payment->payment_status == 'completed')
                                    <span class="badge bg-success">Hoàn thành</span>
                                @elseif($payment->payment_status == 'pending')
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @elseif($payment->payment_status == 'failed')
                                    <span class="badge bg-danger">Thất bại</span>
                                @else
                                    <span class="badge bg-info">Đã hoàn tiền</span>
                                @endif
                            </p>
                        </div>
                        @if($payment->transaction_id)
                        <div class="col-md-12 mb-2">
                            <small class="text-muted">Mã giao dịch:</small>
                            <p class="fw-bold mb-0"><code>{{ $payment->transaction_id }}</code></p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($payment->receipt_image)
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-image text-primary"></i> Ảnh biên lai:
                    </label>
                    <div class="mt-2 p-2 bg-light rounded border">
                        <a href="{{ asset('storage/' . $payment->receipt_image) }}" target="_blank">
                            <img src="{{ asset('storage/' . $payment->receipt_image) }}" 
                                 alt="Biên lai" 
                                 class="img-thumbnail shadow-sm" 
                                 style="max-width: 400px; width: 100%; cursor: pointer;">
                        </a>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="fas fa-info-circle"></i> Click vào ảnh để xem kích thước lớn
                        </p>
                    </div>
                </div>
                @endif

                <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-check-circle text-success"></i> Trạng thái thanh toán <span class="text-danger">*</span>
                        </label>
                        <select name="payment_status" class="form-select form-select-lg @error('payment_status') is-invalid @enderror" required>
                            <option value="pending" {{ old('payment_status', $payment->payment_status) == 'pending' ? 'selected' : '' }}>
                                ⏳ Chờ xử lý
                            </option>
                            <option value="completed" {{ old('payment_status', $payment->payment_status) == 'completed' ? 'selected' : '' }}>
                                ✅ Hoàn thành (Thanh toán thành công)
                            </option>
                            <option value="failed" {{ old('payment_status', $payment->payment_status) == 'failed' ? 'selected' : '' }}>
                                ❌ Thất bại
                            </option>
                            <option value="refunded" {{ old('payment_status', $payment->payment_status) == 'refunded' ? 'selected' : '' }}>
                                🔄 Đã hoàn tiền
                            </option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Chọn trạng thái thanh toán. Nếu chọn "Hoàn thành", booking sẽ tự động chuyển sang trạng thái "Đã xác nhận".
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                                  placeholder="Thêm ghi chú về thanh toán này (nếu cần)">{{ old('notes', $payment->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                        <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đặt phòng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Mã đặt phòng:</label>
                    <p class="fw-bold mb-0">
                        <a href="{{ route('admin.bookings.show', $payment->booking_id) }}">
                            #{{ $payment->booking_id }}
                        </a>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Khách hàng:</label>
                    <p class="fw-bold mb-0">{{ $payment->booking->user->name ?? '-' }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Phòng:</label>
                    <p class="fw-bold mb-0">{{ $payment->booking->room->room_number ?? '-' }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Ngày nhận:</label>
                    <p class="fw-bold mb-0">{{ $payment->booking->check_in_date->format('d/m/Y') ?? '-' }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Ngày trả:</label>
                    <p class="fw-bold mb-0">{{ $payment->booking->check_out_date->format('d/m/Y') ?? '-' }}</p>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="text-muted small">Tổng tiền:</label>
                    <h5 class="text-primary mb-0">{{ number_format($payment->booking->total_price ?? 0) }} VNĐ</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

