@extends('layouts.admin')

@section('title', 'Đặt phòng cho khách')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle"></i> Đặt phòng cho khách</h2>
    <a href="{{ route('admin.employee.checkout.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Thông tin đặt phòng</h5>

                <form action="{{ route('admin.employee.bookings.store') }}" method="POST" id="bookingForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khách hàng *</label>
                            <div class="input-group">
                                <select name="user_id" 
                                        class="form-select @error('user_id') is-invalid @enderror" 
                                        id="user_id"
                                        required>
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                                    <i class="fas fa-plus"></i> Tạo mới
                                </button>
                            </div>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Nếu khách hàng chưa có trong hệ thống, click "Tạo mới" để thêm</small>
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
                            @if(isset($selectedRoomId) && $selectedRoomId)
                                @php
                                    $selectedRoom = $rooms->firstWhere('id', $selectedRoomId);
                                @endphp
                                @if($selectedRoom)
                                    <div class="alert alert-info mt-2 mb-0">
                                        <small>
                                            <i class="fas fa-info-circle"></i> 
                                            <strong>Phòng đã chọn:</strong> {{ $selectedRoom->room_number }} - {{ $selectedRoom->room_type }} | 
                                            <strong>Giá:</strong> {{ number_format($selectedRoom->price_per_night) }} VNĐ/đêm | 
                                            <strong>Sức chứa:</strong> {{ $selectedRoom->capacity }} người
                                        </small>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày nhận phòng *</label>
                            <input type="date" name="check_in_date" 
                                   class="form-control @error('check_in_date') is-invalid @enderror" 
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ old('check_in_date', $selectedCheckInDate ?? date('Y-m-d')) }}" 
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
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ old('check_out_date', $selectedCheckOutDate ?? date('Y-m-d', strtotime('+1 day'))) }}" 
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
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yêu cầu đặc biệt</label>
                        <textarea name="special_requests" class="form-control" rows="3" 
                                  placeholder="Ví dụ: Giường phụ, phòng không hút thuốc, tầng cao...">{{ old('special_requests') }}</textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Lưu ý:</strong> Đặt phòng sẽ tự động được xác nhận (status: confirmed).
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Tạo đặt phòng
                        </button>
                        <a href="{{ route('admin.employee.checkout.index') }}" class="btn btn-secondary">
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

                    <div id="priceCalculation" class="d-none">
                        <h6 class="mb-3">Tính toán giá:</h6>
                        <div class="mb-2">
                            <small class="text-muted">Số đêm:</small>
                            <div class="fw-bold" id="nightsCount">-</div>
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

<!-- Modal Tạo khách hàng mới -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCustomerModalLabel">
                    <i class="fas fa-user-plus"></i> Tạo khách hàng mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCustomerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Họ tên *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">CCCD/CMND</label>
                        <input type="text" name="cccd" class="form-control">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Hệ thống sẽ tự động tạo mật khẩu cho khách hàng. Khách hàng có thể đổi mật khẩu sau.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tạo khách hàng
                    </button>
                </div>
            </form>
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
    const roomPriceSpan = document.getElementById('roomPrice');
    const roomCapacitySpan = document.getElementById('roomCapacity');
    const capacityHint = document.getElementById('capacityHint');
    const numberOfGuestsInput = document.getElementById('number_of_guests');
    
    let currentPricePerNight = 0;
    let currentCapacity = 0;
    let selectedRoomId = null; // Lưu phòng đã chọn trước đó
    
    // Hàm load danh sách phòng trống
    function loadAvailableRooms() {
        const checkInDateInput = document.getElementById('check_in_date');
        const checkOutDateInput = document.getElementById('check_out_date');
        
        if (!checkInDateInput.value || !checkOutDateInput.value) {
            return;
        }
        
        // Lưu phòng đang chọn
        selectedRoomId = roomSelect.value;
        
        // Hiển thị loading
        roomSelect.disabled = true;
        roomSelect.innerHTML = '<option value="">Đang tải danh sách phòng...</option>';
        
        const url = '{{ route("admin.employee.bookings.availableRooms") }}';
        const params = new URLSearchParams({
            check_in_date: checkInDateInput.value,
            check_out_date: checkOutDateInput.value
        });
        
        fetch(`${url}?${params}`)
            .then(response => response.json())
            .then(data => {
                roomSelect.disabled = false;
                roomSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
                
                if (data.rooms && data.rooms.length > 0) {
                    data.rooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        option.textContent = room.display_text;
                        option.setAttribute('data-price', room.price_per_night);
                        option.setAttribute('data-capacity', room.capacity);
                        
                        // Khôi phục phòng đã chọn nếu còn trống
                        if (selectedRoomId == room.id) {
                            option.selected = true;
                        }
                        
                        roomSelect.appendChild(option);
                    });
                    
                    // Trigger change event nếu có phòng được chọn
                    if (selectedRoomId && roomSelect.value == selectedRoomId) {
                        roomSelect.dispatchEvent(new Event('change'));
                    } else {
                        // Reset nếu phòng đã chọn không còn trống
                        selectedRoomId = null;
                        roomInfoDiv.classList.add('d-none');
                        noRoomSelectedDiv.classList.remove('d-none');
                        priceCalculationDiv.classList.add('d-none');
                    }
                } else {
                    roomSelect.innerHTML = '<option value="">Không có phòng trống trong khoảng thời gian này</option>';
                    roomInfoDiv.classList.add('d-none');
                    noRoomSelectedDiv.classList.remove('d-none');
                    priceCalculationDiv.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                roomSelect.disabled = false;
                roomSelect.innerHTML = '<option value="">Lỗi khi tải danh sách phòng</option>';
            });
    }
    
    roomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            currentPricePerNight = parseFloat(selectedOption.getAttribute('data-price'));
            currentCapacity = parseInt(selectedOption.getAttribute('data-capacity'));
            
            roomInfoDiv.classList.remove('d-none');
            noRoomSelectedDiv.classList.add('d-none');
            
            const roomText = selectedOption.textContent.split(' - ');
            document.getElementById('roomNumber').textContent = roomText[0];
            document.getElementById('roomType').textContent = roomText[1] ? roomText[1].split(' (')[0] : '';
            
            roomPriceSpan.textContent = new Intl.NumberFormat('vi-VN').format(currentPricePerNight) + ' VNĐ';
            roomCapacitySpan.textContent = currentCapacity + ' người';
            
            capacityHint.textContent = 'Tối đa ' + currentCapacity + ' người';
            numberOfGuestsInput.setAttribute('max', currentCapacity);
            
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
        
        const checkIn = new Date(checkInDateInput.value + 'T' + checkInTimeInput.value);
        const checkOut = new Date(checkOutDateInput.value + 'T' + checkOutTimeInput.value);
        
        if (checkOut <= checkIn) {
            priceCalculationDiv.classList.add('d-none');
            return;
        }
        
        const isSameDay = checkIn.toDateString() === checkOut.toDateString();
        let totalPrice = 0;
        let displayText = '';
        
        if (isSameDay) {
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const hours = Math.max(1, Math.ceil(timeDiff / (1000 * 60 * 60)));
            const pricePerHour = currentPricePerNight / 24;
            totalPrice = hours * pricePerHour;
            const minPrice = currentPricePerNight / 3;
            if (totalPrice < minPrice) {
                totalPrice = minPrice;
            }
            displayText = hours + ' giờ';
        } else {
            const checkInDateOnly = new Date(checkInDateInput.value + 'T00:00:00');
            const checkOutDateOnly = new Date(checkOutDateInput.value + 'T00:00:00');
            const timeDiff = checkOutDateOnly.getTime() - checkInDateOnly.getTime();
            const daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
            let nights = daysDiff;
            
            const checkInTime = checkInTimeInput.value;
            const checkOutTime = checkOutTimeInput.value;
            if (checkInTime && checkOutTime && checkOutTime >= checkInTime) {
                nights += 1;
            } else {
                nights += 1;
            }
            
            if (checkOutTime > '12:00') {
                nights += 0.5;
            }
            
            totalPrice = nights * currentPricePerNight;
            displayText = nights + (nights % 1 === 0 ? ' đêm' : ' đêm');
        }
        
        nightsCountSpan.textContent = displayText;
        totalPriceSpan.textContent = new Intl.NumberFormat('vi-VN').format(Math.ceil(totalPrice)) + ' VNĐ';
        priceCalculationDiv.classList.remove('d-none');
    }
    
    const checkInDateInput = document.getElementById('check_in_date');
    const checkInTimeInput = document.getElementById('check_in_time');
    const checkOutDateInput = document.getElementById('check_out_date');
    const checkOutTimeInput = document.getElementById('check_out_time');
    
    if (checkInDateInput && checkOutDateInput && checkInTimeInput && checkOutTimeInput) {
        // Khi thay đổi ngày, load lại danh sách phòng trống
        checkInDateInput.addEventListener('change', function() {
            if (checkOutDateInput.value) {
                const checkInDate = new Date(checkInDateInput.value);
                const checkOutDate = new Date(checkOutDateInput.value);
                
                if (checkOutDate < checkInDate) {
                    alert('Ngày trả phòng phải sau hoặc bằng ngày nhận phòng!');
                    checkOutDateInput.value = '';
                    return;
                }
                
                loadAvailableRooms();
            }
            calculatePrice();
        });
        
        checkOutDateInput.addEventListener('change', function() {
            if (checkInDateInput.value) {
                const checkInDate = new Date(checkInDateInput.value);
                const checkOutDate = new Date(checkOutDateInput.value);
                
                if (checkOutDate < checkInDate) {
                    alert('Ngày trả phòng phải sau hoặc bằng ngày nhận phòng!');
                    checkOutDateInput.value = '';
                    priceCalculationDiv.classList.add('d-none');
                    return;
                }
                
                loadAvailableRooms();
            }
            
            if (checkInDateInput.value === checkOutDateInput.value) {
                if (checkOutTimeInput.value <= checkInTimeInput.value) {
                    alert('Khi cùng ngày, giờ trả phòng phải sau giờ nhận phòng!');
                    checkOutTimeInput.value = '';
                }
            }
            
            calculatePrice();
        });
        
        checkInTimeInput.addEventListener('change', calculatePrice);
        checkOutTimeInput.addEventListener('change', function() {
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
        
        // Load danh sách phòng khi trang load nếu đã có ngày
        if (checkInDateInput.value && checkOutDateInput.value) {
            loadAvailableRooms();
        }
        
        if (checkInDateInput.value && checkOutDateInput.value && 
            checkInTimeInput.value && checkOutTimeInput.value && roomSelect.value) {
            calculatePrice();
        }
    }
    
    if (roomSelect.value) {
        roomSelect.dispatchEvent(new Event('change'));
    }
    
    // Xử lý tạo khách hàng mới
    const createCustomerForm = document.getElementById('createCustomerForm');
    const userSelect = document.getElementById('user_id');
    
    if (createCustomerForm) {
        createCustomerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
            
            const formData = new FormData(this);
            
            fetch('{{ route("admin.employee.bookings.createCustomer") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Thêm khách hàng mới vào dropdown
                    const option = document.createElement('option');
                    option.value = data.user.id;
                    option.textContent = data.user.name + ' (' + data.user.email + ')';
                    option.selected = true;
                    userSelect.appendChild(option);
                    
                    // Đóng modal và reset form
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createCustomerModal'));
                    modal.hide();
                    createCustomerForm.reset();
                    
                    // Hiển thị thông báo
                    alert('Tạo khách hàng thành công!');
                } else {
                    alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
</script>
@endpush
@endsection

