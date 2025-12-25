@extends('layouts.admin')

@section('title', 'Thanh toán - Booking #' . $booking->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-credit-card"></i> Thanh toán - Booking #{{ $booking->id }}</h2>
    <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin thanh toán</h5>
            </div>
            <div class="card-body">
                <!-- Thông tin booking -->
                <div class="alert alert-info mb-4">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Thông tin đặt phòng:</h6>
                    <p class="mb-1"><strong>Khách hàng:</strong> {{ $booking->user->name }}</p>
                    <p class="mb-1"><strong>Phòng:</strong> {{ $booking->room->room_number }} - {{ $booking->room->room_type }}</p>
                    <p class="mb-1"><strong>Tổng tiền:</strong> <span class="text-primary fw-bold">{{ number_format($booking->total_price) }} VNĐ</span></p>
                </div>

                @if($booking->payment && $booking->payment->payment_status === 'completed')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Đặt phòng này đã được thanh toán.
                    </div>
                @else
                    <form action="{{ route('admin.employee.payments.store', $booking->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Số tiền (VNĐ) *</label>
                            <input type="number" name="amount" id="amount" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   value="{{ old('amount', $booking->payment ? $booking->payment->amount : $booking->total_price) }}" 
                                   min="0" step="1000" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Mặc định: {{ number_format($booking->total_price) }} VNĐ</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phương thức thanh toán *</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="cash" {{ old('payment_method', $booking->payment ? $booking->payment->payment_method : 'cash') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                <option value="bank_transfer_qr" {{ old('payment_method', $booking->payment ? $booking->payment->payment_method : '') == 'bank_transfer_qr' ? 'selected' : '' }}>Chuyển khoản QR</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái thanh toán *</label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="pending" {{ old('payment_status', $booking->payment ? $booking->payment->payment_status : 'pending') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                                <option value="completed" {{ old('payment_status', $booking->payment ? $booking->payment->payment_status : '') == 'completed' ? 'selected' : '' }}>Đã thanh toán</option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Chọn "Đã thanh toán" nếu khách đã thanh toán tại quầy</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã giao dịch (nếu có)</label>
                            <input type="text" name="transaction_id" 
                                   class="form-control @error('transaction_id') is-invalid @enderror" 
                                   value="{{ old('transaction_id', $booking->payment ? $booking->payment->transaction_id : '') }}" 
                                   placeholder="Nhập mã giao dịch nếu có">
                            @error('transaction_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Ghi chú thêm (nếu có)">{{ old('notes', $booking->payment ? $booking->payment->notes : '') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.employee.checkout.show', $booking->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thanh toán
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Thông tin booking -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đặt phòng</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Mã đặt phòng:</small>
                    <p class="fw-bold">#{{ $booking->id }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Khách hàng:</small>
                    <p class="fw-bold">{{ $booking->user->name }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Phòng:</small>
                    <p class="fw-bold">{{ $booking->room->room_number }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Check-in:</small>
                    <p class="fw-bold">{{ $booking->check_in_date->format('d/m/Y') }} {{ $booking->check_in_time ?? '14:00' }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Checkout:</small>
                    <p class="fw-bold">{{ $booking->check_out_date->format('d/m/Y') }} {{ $booking->check_out_time ?? '12:00' }}</p>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Tổng tiền:</small>
                    <p class="fw-bold text-primary fs-5">{{ number_format($booking->total_price) }} VNĐ</p>
                </div>
            </div>
        </div>

        <!-- Trạng thái thanh toán hiện tại -->
        @if($booking->payment)
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Trạng thái hiện tại</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Trạng thái:</small>
                        <p class="fw-bold">
                            @if($booking->payment->payment_status == 'completed')
                                <span class="badge bg-success">Đã thanh toán</span>
                            @elseif($booking->payment->payment_status == 'pending')
                                <span class="badge bg-warning">Chờ thanh toán</span>
                            @else
                                <span class="badge bg-danger">{{ $booking->payment->payment_status }}</span>
                            @endif
                        </p>
                    </div>
                    @if($booking->payment->payment_method)
                    <div class="mb-2">
                        <small class="text-muted">Phương thức:</small>
                        <p class="fw-bold">
                            @if($booking->payment->payment_method == 'cash')
                                Tiền mặt
                            @elseif($booking->payment->payment_method == 'bank_transfer_qr')
                                Chuyển khoản QR
                            @else
                                {{ $booking->payment->payment_method }}
                            @endif
                        </p>
                    </div>
                    @endif
                    @if($booking->payment->payment_date)
                    <div class="mb-0">
                        <small class="text-muted">Ngày thanh toán:</small>
                        <p class="fw-bold">{{ $booking->payment->payment_date->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tự động điền số tiền mặc định
        const amountInput = document.getElementById('amount');
        const bookingTotal = {{ $booking->total_price }};
        
        if (!amountInput.value || amountInput.value == 0) {
            amountInput.value = bookingTotal;
        }
    });
</script>
@endpush
@endsection

