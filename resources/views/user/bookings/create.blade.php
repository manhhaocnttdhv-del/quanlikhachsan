@extends('layouts.app')

@section('title', 'Đặt phòng')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-calendar-plus"></i> Đặt phòng</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin đặt phòng</h5>

                    <form action="{{ route('user.bookings.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày nhận phòng *</label>
                                <input type="date" name="check_in_date" 
                                       class="form-control @error('check_in_date') is-invalid @enderror" 
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', request('check_in_date')) }}" 
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
                                       value="{{ old('check_out_date', request('check_out_date')) }}" 
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

                        <div class="mb-3">
                            <label class="form-label">Số người *</label>
                            <input type="number" name="number_of_guests" 
                                   class="form-control @error('number_of_guests') is-invalid @enderror" 
                                   min="1" max="{{ $room->capacity }}" 
                                   value="{{ old('number_of_guests', request('number_of_guests', 1)) }}" 
                                   required>
                            <small class="text-muted">Tối đa {{ $room->capacity }} người</small>
                            @error('number_of_guests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yêu cầu đặc biệt</label>
                            <textarea name="special_requests" class="form-control" rows="3" 
                                      placeholder="Ví dụ: Giường phụ, phòng không hút thuốc, tầng cao...">{{ old('special_requests') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Xác nhận đặt phòng
                            </button>
                            <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
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
                    
                    @if($room->image)
                        <img src="{{ asset('storage/' . $room->image) }}" class="img-fluid rounded mb-3" alt="{{ $room->room_number }}">
                    @endif

                    <h6>Phòng {{ $room->room_number }}</h6>
                    <p class="text-muted mb-2">{{ $room->room_type }}</p>
                    
                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giá/đêm:</span>
                        <strong>{{ number_format($room->price_per_night) }} VNĐ</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Sức chứa:</span>
                        <strong>{{ $room->capacity }} người</strong>
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
                            <div class="fw-bold">{{ number_format($room->price_per_night) }} VNĐ</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Tổng tiền:</h6>
                            <h5 class="text-primary mb-0" id="totalPrice">0 VNĐ</h5>
                        </div>
                    </div>

                    @if($room->amenities)
                        <hr>
                        <h6 class="mb-2">Tiện nghi:</h6>
                        <ul class="list-unstyled small">
                            @foreach($room->amenities as $amenity)
                                <li><i class="fas fa-check text-success"></i> {{ $amenity }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tự động tính tổng tiền khi thay đổi ngày
    const checkInInput = document.querySelector('input[name="check_in_date"]');
    const checkOutInput = document.querySelector('input[name="check_out_date"]');
    const pricePerNight = {{ $room->price_per_night }};
    const priceCalculationDiv = document.getElementById('priceCalculation');
    const nightsCountSpan = document.getElementById('nightsCount');
    const totalPriceSpan = document.getElementById('totalPrice');
    
    function calculatePrice() {
        const checkInDateInput = document.getElementById('check_in_date');
        const checkInTimeInput = document.getElementById('check_in_time');
        const checkOutDateInput = document.getElementById('check_out_date');
        const checkOutTimeInput = document.getElementById('check_out_time');
        
        if (!checkInDateInput.value || !checkOutDateInput.value || 
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
            
            const pricePerHour = pricePerNight / 24;
            totalPrice = hours * pricePerHour;
            
            // Tối thiểu = 1/3 giá đêm
            const minPrice = pricePerNight / 3;
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
            
            totalPrice = nights * pricePerNight;
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
            checkInTimeInput.value && checkOutTimeInput.value) {
            calculatePrice();
        }
    }
</script>
@endpush
@endsection

