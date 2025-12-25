@extends('layouts.admin')

@section('title', 'Tạo đặt phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle"></i> Tạo đặt phòng</h2>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Thông tin đặt phòng</h5>

                <form action="{{ route('admin.bookings.store') }}" method="POST" id="bookingForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khách hàng *</label>
                            <select name="user_id" 
                                    class="form-select @error('user_id') is-invalid @enderror" 
                                    required>
                                <option value="">-- Chọn khách hàng --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phòng *</label>
                            <select name="room_id" 
                                    class="form-select @error('room_id') is-invalid @enderror" 
                                    id="room_id"
                                    required>
                                <option value="">-- Chọn phòng --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" 
                                            data-price="{{ $room->price_per_night }}"
                                            data-capacity="{{ $room->capacity }}"
                                            {{ old('room_id', $selectedRoomId ?? '') == $room->id ? 'selected' : '' }}>
                                        {{ $room->room_number }} - {{ $room->room_type }} 
                                        ({{ number_format($room->price_per_night) }} VNĐ/đêm)
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày nhận phòng *</label>
                            <input type="date" name="check_in_date" 
                                   class="form-control @error('check_in_date') is-invalid @enderror" 
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ old('check_in_date', $selectedCheckInDate ?? '') }}" 
                                   id="check_in_date"
                                   required>
                            @error('check_in_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giờ nhận phòng *</label>
                            <input type="time" name="check_in_time" 
                                   class="form-control @error('check_in_time') is-invalid @enderror" 
                                   value="{{ old('check_in_time', '14:00') }}" 
                                   id="check_in_time"
                                   required>
                            <small class="text-muted">Giờ check-in mặc định: 14:00</small>
                            @error('check_in_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày trả phòng *</label>
                            <input type="date" name="check_out_date" 
                                   class="form-control @error('check_out_date') is-invalid @enderror" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   value="{{ old('check_out_date', $selectedCheckOutDate ?? '') }}" 
                                   id="check_out_date"
                                   required>
                            @error('check_out_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giờ trả phòng *</label>
                            <input type="time" name="check_out_time" 
                                   class="form-control @error('check_out_time') is-invalid @enderror" 
                                   value="{{ old('check_out_time', '12:00') }}" 
                                   id="check_out_time"
                                   required>
                            <small class="text-muted">Giờ check-out mặc định: 12:00</small>
                            @error('check_out_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số người *</label>
                            <input type="number" name="number_of_guests" 
                                   class="form-control @error('number_of_guests') is-invalid @enderror" 
                                   min="1" 
                                   id="number_of_guests"
                                   value="{{ old('number_of_guests', 1) }}" 
                                   required>
                            <small class="text-muted" id="capacityHint">Chọn phòng để xem sức chứa</small>
                            @error('number_of_guests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái *</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                <option value="checked_in" {{ old('status') == 'checked_in' ? 'selected' : '' }}>Đã nhận phòng</option>
                                <option value="checked_out" {{ old('status') == 'checked_out' ? 'selected' : '' }}>Đã trả phòng</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yêu cầu đặc biệt</label>
                        <textarea name="special_requests" class="form-control" rows="3" 
                                  placeholder="Ví dụ: Giường phụ, phòng không hút thuốc, tầng cao...">{{ old('special_requests') }}</textarea>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-money-bill-wave"></i> Thanh toán (Tùy chọn)</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phương thức thanh toán</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                <option value="">-- Chưa thanh toán --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                <option value="bank_transfer_qr" {{ old('payment_method') == 'bank_transfer_qr' ? 'selected' : '' }}>QR Chuyển khoản</option>
                            </select>
                            <small class="text-muted">Chọn phương thức thanh toán nếu khách đã thanh toán</small>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái thanh toán</label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                <option value="">-- Chưa thanh toán --</option>
                                <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Đang chờ</option>
                                <option value="completed" {{ old('payment_status') == 'completed' ? 'selected' : '' }}>Đã thanh toán</option>
                            </select>
                            <small class="text-muted">Chọn trạng thái thanh toán</small>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Lưu ý:</strong> Nếu chọn "Tiền mặt" và "Đã thanh toán", hệ thống sẽ tự động tạo thanh toán và cập nhật trạng thái đặt phòng.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Tạo đặt phòng
                        </button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-body">
                <h5 class="card-title mb-3">Thông tin phòng</h5>
                
                <div id="roomInfo" class="d-none">
                    <div id="roomImage"></div>
                    <h6 id="roomNumber"></h6>
                    <p class="text-muted mb-2" id="roomType"></p>
                    
                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giá/đêm:</span>
                        <strong id="roomPrice">-</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Sức chứa:</span>
                        <strong id="roomCapacity">-</strong>
                    </div>

                    <hr>

                    <!-- Hiển thị tính toán giá -->
                    <div id="priceCalculation" class="d-none">
                        <h6 class="mb-3">Tính toán giá:</h6>
                        <div class="mb-2">
                            <small class="text-muted">Số đêm:</small>
                            <div class="fw-bold" id="nightsCount">-</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Giá/đêm:</small>
                            <div class="fw-bold" id="pricePerNight">-</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Tổng tiền:</h6>
                            <h5 class="text-primary mb-0" id="totalPrice">0 VNĐ</h5>
                        </div>
                    </div>
                </div>
                
                <div id="noRoomSelected" class="text-muted text-center py-4">
                    <i class="fas fa-info-circle"></i> Vui lòng chọn phòng để xem thông tin
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const roomSelect = document.getElementById('room_id');
    const roomInfoDiv = document.getElementById('roomInfo');
    const noRoomSelectedDiv = document.getElementById('noRoomSelected');
    const priceCalculationDiv = document.getElementById('priceCalculation');
    const nightsCountSpan = document.getElementById('nightsCount');
    const totalPriceSpan = document.getElementById('totalPrice');
    const pricePerNightSpan = document.getElementById('pricePerNight');
    const roomPriceSpan = document.getElementById('roomPrice');
    const roomCapacitySpan = document.getElementById('roomCapacity');
    const capacityHint = document.getElementById('capacityHint');
    const numberOfGuestsInput = document.getElementById('number_of_guests');
    
    let currentPricePerNight = 0;
    let currentCapacity = 0;
    
    // Khi chọn phòng
    roomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            currentPricePerNight = parseFloat(selectedOption.getAttribute('data-price'));
            currentCapacity = parseInt(selectedOption.getAttribute('data-capacity'));
            
            // Hiển thị thông tin phòng
            roomInfoDiv.classList.remove('d-none');
            noRoomSelectedDiv.classList.add('d-none');
            
            roomPriceSpan.textContent = new Intl.NumberFormat('vi-VN').format(currentPricePerNight) + ' VNĐ';
            roomCapacitySpan.textContent = currentCapacity + ' người';
            pricePerNightSpan.textContent = new Intl.NumberFormat('vi-VN').format(currentPricePerNight) + ' VNĐ';
            
            // Cập nhật hint và max cho number_of_guests
            capacityHint.textContent = 'Tối đa ' + currentCapacity + ' người';
            numberOfGuestsInput.setAttribute('max', currentCapacity);
            
            // Tính lại giá nếu đã có ngày
            calculatePrice();
        } else {
            roomInfoDiv.classList.add('d-none');
            noRoomSelectedDiv.classList.remove('d-none');
            priceCalculationDiv.classList.add('d-none');
        }
    });
    
    function calculatePrice() {
        const checkInDateInput = document.getElementById('check_in_date');
        const checkInTimeInput = document.getElementById('check_in_time');
        const checkOutDateInput = document.getElementById('check_out_date');
        const checkOutTimeInput = document.getElementById('check_out_time');
        
        if (!roomSelect.value || !currentPricePerNight || 
            !checkInDateInput.value || !checkOutDateInput.value || 
            !checkInTimeInput.value || !checkOutTimeInput.value) {
            priceCalculationDiv.classList.add('d-none');
            return;
        }
        
        // Kết hợp date + time
        const checkIn = new Date(checkInDateInput.value + 'T' + checkInTimeInput.value);
        const checkOut = new Date(checkOutDateInput.value + 'T' + checkOutTimeInput.value);
        
        if (checkOut <= checkIn) {
            priceCalculationDiv.classList.add('d-none');
            return;
        }
        
        // Kiểm tra nếu cùng ngày (tính theo giờ)
        const isSameDay = checkIn.toDateString() === checkOut.toDateString();
        
        let totalPrice = 0;
        let displayText = '';
        
        if (isSameDay) {
            // Tính theo giờ nếu cùng ngày
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const hours = Math.max(1, Math.ceil(timeDiff / (1000 * 60 * 60))); // Tối thiểu 1 giờ
            
            const pricePerHour = currentPricePerNight / 24;
            totalPrice = hours * pricePerHour;
            
            // Tối thiểu = 1/3 giá đêm
            const minPrice = currentPricePerNight / 3;
            if (totalPrice < minPrice) {
                totalPrice = minPrice;
            }
            
            displayText = hours + ' giờ';
        } else {
            // Tính theo đêm nếu khác ngày
            // Tính số đêm dựa trên cả date và time
            const checkInDateOnly = new Date(checkInDateInput.value + 'T00:00:00');
            const checkOutDateOnly = new Date(checkOutDateInput.value + 'T00:00:00');
            const timeDiff = checkOutDateOnly.getTime() - checkInDateOnly.getTime();
            const daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
            let nights = daysDiff;
            
            // Nếu check-out time >= check-in time, tính thêm 1 đêm (đêm cuối)
            const checkInTime = checkInTimeInput.value;
            const checkOutTime = checkOutTimeInput.value;
            if (checkInTime && checkOutTime) {
                if (checkOutTime >= checkInTime) {
                    nights += 1;
                }
            } else {
                // Mặc định tính đêm cuối
                nights += 1;
            }
            
            // Nếu check-out time > 12:00, tính thêm 0.5 đêm (late check-out)
            if (checkOutTime > '12:00') {
                nights += 0.5;
            }
            
            totalPrice = nights * currentPricePerNight;
            displayText = nights + (nights % 1 === 0 ? ' đêm' : ' đêm (có thêm nửa đêm)');
        }
        
        // Hiển thị kết quả
        nightsCountSpan.textContent = displayText;
        totalPriceSpan.textContent = new Intl.NumberFormat('vi-VN').format(Math.ceil(totalPrice)) + ' VNĐ';
        priceCalculationDiv.classList.remove('d-none');
    }
    
    const checkInDateInput = document.getElementById('check_in_date');
    const checkInTimeInput = document.getElementById('check_in_time');
    const checkOutDateInput = document.getElementById('check_out_date');
    const checkOutTimeInput = document.getElementById('check_out_time');
    
    if (checkInDateInput && checkOutDateInput && checkInTimeInput && checkOutTimeInput) {
        // Lắng nghe sự kiện thay đổi
        checkInDateInput.addEventListener('change', calculatePrice);
        checkInTimeInput.addEventListener('change', calculatePrice);
        checkOutDateInput.addEventListener('change', function() {
            const checkInDate = new Date(checkInDateInput.value);
            const checkOutDate = new Date(checkOutDateInput.value);
            
            if (checkOutDate < checkInDate) {
                alert('Ngày trả phòng phải sau hoặc bằng ngày nhận phòng!');
                checkOutDateInput.value = '';
                priceCalculationDiv.classList.add('d-none');
                return;
            }
            
            // Nếu cùng ngày, kiểm tra giờ
            if (checkInDateInput.value === checkOutDateInput.value) {
                if (checkOutTimeInput.value <= checkInTimeInput.value) {
                    alert('Khi cùng ngày, giờ trả phòng phải sau giờ nhận phòng!');
                    checkOutTimeInput.value = '';
                }
            }
            
            calculatePrice();
        });
        checkOutTimeInput.addEventListener('change', function() {
            // Kiểm tra nếu cùng ngày
            if (checkInDateInput.value === checkOutDateInput.value) {
                if (checkOutTimeInput.value <= checkInTimeInput.value) {
                    alert('Khi cùng ngày, giờ trả phòng phải sau giờ nhận phòng!');
                    checkOutTimeInput.value = '';
                    priceCalculationDiv.classList.add('d-none');
                    return;
                }
            }
            calculatePrice();
        });
        
        // Tính ngay nếu đã có giá trị
        if (checkInDateInput.value && checkOutDateInput.value && 
            checkInTimeInput.value && checkOutTimeInput.value && roomSelect.value) {
            calculatePrice();
        }
    }
    
    // Khi chọn phòng, tự động tính giá nếu đã có ngày
    if (roomSelect.value) {
        roomSelect.dispatchEvent(new Event('change'));
    }
</script>
@endpush
@endsection

