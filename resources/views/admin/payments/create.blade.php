@extends('layouts.admin')

@section('title', 'Tạo thanh toán mới')

@push('styles')
<style>
    .booking-info {
        border-left: 4px solid #667eea;
        padding-left: 15px;
        margin-top: 10px;
    }
    .booking-card {
        transition: all 0.3s;
        cursor: pointer;
    }
    .booking-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .booking-card.selected {
        border-color: #667eea !important;
        background-color: #f0f4ff;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle"></i> Tạo thanh toán mới</h2>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
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
                <form action="{{ route('admin.payments.store') }}" method="POST" id="paymentForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Chọn đặt phòng *</label>
                        <select name="booking_id" id="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                            <option value="">-- Chọn đặt phòng --</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}" 
                                        data-amount="{{ $booking->total_price }}"
                                        data-user="{{ $booking->user->name }}"
                                        data-room="{{ $booking->room->room_number }}"
                                        data-checkin="{{ $booking->check_in_date->format('d/m/Y') }}"
                                        data-checkout="{{ $booking->check_out_date->format('d/m/Y') }}"
                                        {{ (old('booking_id', isset($selectedBooking) ? $selectedBooking->id : null) == $booking->id) ? 'selected' : '' }}>
                                    #{{ $booking->id }} - {{ $booking->user->name }} - Phòng {{ $booking->room->room_number }} 
                                    ({{ number_format($booking->total_price) }} VNĐ)
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($bookings->isEmpty())
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle"></i> Không có đặt phòng nào chưa thanh toán.
                            </div>
                        @endif
                    </div>

                    <div id="bookingInfo" class="booking-info" style="display: none;">
                        <h6 class="text-primary">Thông tin đặt phòng:</h6>
                        <p class="mb-1"><strong>Khách hàng:</strong> <span id="bookingUser">-</span></p>
                        <p class="mb-1"><strong>Phòng:</strong> <span id="bookingRoom">-</span></p>
                        <p class="mb-1"><strong>Ngày nhận:</strong> <span id="bookingCheckIn">-</span></p>
                        <p class="mb-1"><strong>Ngày trả:</strong> <span id="bookingCheckOut">-</span></p>
                        <p class="mb-0"><strong>Tổng tiền:</strong> <span id="bookingTotal" class="text-primary fw-bold">-</span></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số tiền (VNĐ) *</label>
                        <input type="number" name="amount" id="amount" 
                               class="form-control @error('amount') is-invalid @enderror" 
                               value="{{ old('amount') }}" 
                               min="0" step="1000" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Số tiền sẽ tự động điền khi chọn đặt phòng</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán *</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái thanh toán *</label>
                        <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                            <option value="completed" {{ old('payment_status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mã giao dịch</label>
                        <input type="text" name="transaction_id" 
                               class="form-control @error('transaction_id') is-invalid @enderror" 
                               value="{{ old('transaction_id') }}" 
                               placeholder="Nhập mã giao dịch (nếu có)">
                        @error('transaction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Mã giao dịch từ ngân hàng (nếu có)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="4" placeholder="Ghi chú về thanh toán này">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo thanh toán
                        </button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
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
                <h5 class="mb-0">Danh sách đặt phòng</h5>
            </div>
            <div class="card-body">
                @if($bookings->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không có đặt phòng nào chưa thanh toán.
                    </div>
                @else
                    <div class="list-group">
                        @foreach($bookings as $booking)
                            <div class="list-group-item booking-card" 
                                 data-booking-id="{{ $booking->id }}"
                                 onclick="selectBooking({{ $booking->id }})">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">#{{ $booking->id }}</h6>
                                        <p class="mb-1 small">
                                            <strong>{{ $booking->user->name }}</strong><br>
                                            Phòng: {{ $booking->room->room_number }}<br>
                                            {{ $booking->check_in_date->format('d/m/Y') }} - {{ $booking->check_out_date->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $booking->status == 'pending' ? 'warning' : 'info' }}">
                                            {{ $booking->status == 'pending' ? 'Chờ xử lý' : 'Đã xác nhận' }}
                                        </span>
                                        <p class="mb-0 mt-1 text-primary fw-bold">
                                            {{ number_format($booking->total_price) }} VNĐ
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tự động điền thông tin khi chọn booking
    document.getElementById('booking_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const bookingInfo = document.getElementById('bookingInfo');
        
        if (selectedOption.value) {
            const amount = selectedOption.getAttribute('data-amount');
            const user = selectedOption.getAttribute('data-user');
            const room = selectedOption.getAttribute('data-room');
            const checkIn = selectedOption.getAttribute('data-checkin');
            const checkOut = selectedOption.getAttribute('data-checkout');
            
            // Điền số tiền
            document.getElementById('amount').value = amount;
            
            // Hiển thị thông tin booking
            document.getElementById('bookingUser').textContent = user;
            document.getElementById('bookingRoom').textContent = room;
            document.getElementById('bookingCheckIn').textContent = checkIn;
            document.getElementById('bookingCheckOut').textContent = checkOut;
            document.getElementById('bookingTotal').textContent = new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
            
            bookingInfo.style.display = 'block';
        } else {
            bookingInfo.style.display = 'none';
        }
    });

    // Chọn booking từ danh sách
    function selectBooking(bookingId) {
        document.getElementById('booking_id').value = bookingId;
        document.getElementById('booking_id').dispatchEvent(new Event('change'));
        
        // Highlight selected booking
        document.querySelectorAll('.booking-card').forEach(card => {
            card.classList.remove('selected');
            if (card.getAttribute('data-booking-id') == bookingId) {
                card.classList.add('selected');
            }
        });
    }

    // Format số tiền khi nhập
    document.getElementById('amount').addEventListener('input', function() {
        let value = this.value.replace(/[^\d]/g, '');
        if (value) {
            this.value = parseInt(value);
        }
    });

    // Trigger change event nếu đã có booking được chọn sẵn
    @if(isset($selectedBooking) && $selectedBooking)
        document.getElementById('booking_id').dispatchEvent(new Event('change'));
    @endif
</script>
@endpush

