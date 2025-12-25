@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng #' . $booking->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Chi tiết đặt phòng #{{ $booking->id }}</h2>
    <div>
        @if($booking->status !== 'cancelled')
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
        @endif
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Booking Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin đặt phòng</h5>
                    @if($booking->status == 'pending')
                        <span class="badge bg-warning fs-6">Chờ xử lý</span>
                    @elseif($booking->status == 'confirmed')
                        <span class="badge bg-success fs-6">Đã xác nhận</span>
                    @elseif($booking->status == 'checked_in')
                        <span class="badge bg-info fs-6">Đã nhận phòng</span>
                    @elseif($booking->status == 'checked_out')
                        <span class="badge bg-secondary fs-6">Đã trả phòng</span>
                    @else
                        <span class="badge bg-danger fs-6">Đã hủy</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã đặt phòng:</label>
                        <p class="fw-bold">#{{ $booking->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày đặt:</label>
                        <p class="fw-bold">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin khách hàng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Họ tên:</label>
                        <p class="fw-bold">{{ $booking->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email:</label>
                        <p class="fw-bold">{{ $booking->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số điện thoại:</label>
                        <p class="fw-bold">{{ $booking->user->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">CCCD:</label>
                        <p class="fw-bold">{{ $booking->user->cccd ?? '-' }}</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số phòng:</label>
                        <p class="fw-bold">{{ $booking->room->room_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Loại phòng:</label>
                        <p class="fw-bold">{{ $booking->room->room_type }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Giá/đêm:</label>
                        <p class="fw-bold">{{ number_format($booking->room->price_per_night) }} VNĐ</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sức chứa:</label>
                        <p class="fw-bold">{{ $booking->room->capacity }} người</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Chi tiết đặt phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày nhận phòng:</label>
                        <p class="fw-bold">
                            {{ $booking->check_in_date->format('d/m/Y') }}
                                @if($booking->check_in_time)
                                    <span class="text-muted">({{ substr($booking->check_in_time, 0, 5) }})</span>
                                @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày trả phòng:</label>
                        <p class="fw-bold">
                            {{ $booking->check_out_date->format('d/m/Y') }}
                                @if($booking->check_out_time)
                                    <span class="text-muted">({{ substr($booking->check_out_time, 0, 5) }})</span>
                                @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">
                            @php
                                // Format date thành Y-m-d trước khi nối với time
                                $checkInDateStr = $booking->check_in_date->format('Y-m-d');
                                $checkOutDateStr = $booking->check_out_date->format('Y-m-d');
                                // Lấy phần H:i từ time string (bỏ seconds nếu có)
                                $checkInTime = $booking->check_in_time ? substr($booking->check_in_time, 0, 5) : '14:00';
                                $checkOutTime = $booking->check_out_time ? substr($booking->check_out_time, 0, 5) : '12:00';
                                
                                $checkIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $checkInDateStr . ' ' . $checkInTime);
                                $checkOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $checkOutDateStr . ' ' . $checkOutTime);
                                $isSameDay = $checkIn->isSameDay($checkOut);
                            @endphp
                            @if($isSameDay)
                                Số giờ:
                            @else
                                Số đêm:
                            @endif
                        </label>
                        <p class="fw-bold">
                            @if($isSameDay)
                                {{ $checkIn->diffInHours($checkOut) }} giờ
                            @else
                                @php
                                    // Tính số đêm dựa trên cả date và time
                                    $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
                                    
                                    // Nếu check-out time >= check-in time, tính thêm 1 đêm (đêm cuối)
                                    if ($checkInTime && $checkOutTime && strpos($checkInTime, ':') !== false && strpos($checkOutTime, ':') !== false) {
                                        $checkInTimeParts = explode(':', $checkInTime);
                                        $checkOutTimeParts = explode(':', $checkOutTime);
                                        $checkInHour = (int)$checkInTimeParts[0];
                                        $checkInMinute = (int)$checkInTimeParts[1];
                                        $checkOutHour = (int)$checkOutTimeParts[0];
                                        $checkOutMinute = (int)$checkOutTimeParts[1];
                                        
                                        if ($checkOutHour > $checkInHour || ($checkOutHour == $checkInHour && $checkOutMinute >= $checkInMinute)) {
                                            $nights += 1;
                                        }
                                    } else {
                                        $nights += 1;
                                    }
                                    
                                    // Nếu check-out time > 12:00, tính thêm 0.5 đêm (late check-out)
                                    if ($checkOutTime && strpos($checkOutTime, ':') !== false) {
                                        $timeParts = explode(':', $checkOutTime);
                                        $hour = (int)$timeParts[0];
                                        if ($hour > 12) {
                                            $nights += 0.5;
                                        }
                                    }
                                @endphp
                                
                                {{ $nights }} đêm
                            @endif
                        </p>

                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số người:</label>
                        <p class="fw-bold">{{ $booking->number_of_guests }} người</p>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div class="mb-3">
                        <label class="text-muted small">Yêu cầu đặc biệt:</label>
                        <p class="mb-0">{{ $booking->special_requests }}</p>
                    </div>
                @endif

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tổng tiền:</h5>
                    <h4 class="text-primary mb-0">{{ number_format($booking->total_price) }} VNĐ</h4>
                </div>
            </div>
        </div>

        <!-- Payment Info Card -->
        @if($booking->payment)
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Thông tin thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Trạng thái thanh toán:</label>
                            <p class="mb-0">
                                @if($booking->payment->payment_status == 'completed')
                                    <span class="badge bg-success">Hoàn thành</span>
                                @elseif($booking->payment->payment_status == 'pending')
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @elseif($booking->payment->payment_status == 'failed')
                                    <span class="badge bg-danger">Thất bại</span>
                                @else
                                    <span class="badge bg-secondary">Đã hoàn tiền</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phương thức thanh toán:</label>
                            <p class="mb-0">
                                @if($booking->payment->payment_method == 'cash')
                                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                @elseif($booking->payment->payment_method == 'credit_card')
                                    <i class="fas fa-credit-card"></i> Thẻ tín dụng
                                @elseif($booking->payment->payment_method == 'bank_transfer')
                                    <i class="fas fa-university"></i> Chuyển khoản
                                @elseif($booking->payment->payment_method == 'bank_transfer_qr')
                                    <i class="fas fa-qrcode"></i> QR Chuyển khoản
                                @elseif($booking->payment->payment_method == 'momo')
                                    <i class="fas fa-mobile-alt"></i> MoMo
                                @elseif($booking->payment->payment_method == 'vnpay')
                                    <i class="fas fa-wallet"></i> VNPay
                                @else
                                    {{ $booking->payment->payment_method }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Số tiền:</label>
                            <p class="fw-bold mb-0">{{ number_format($booking->payment->amount) }} VNĐ</p>
                        </div>
                        @if($booking->payment->payment_date)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Ngày thanh toán:</label>
                                <p class="mb-0">{{ $booking->payment->payment_date->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        @if($booking->payment->transaction_id)
                            <div class="col-md-12 mb-3">
                                <label class="text-muted small">Mã giao dịch:</label>
                                <p class="mb-0"><code>{{ $booking->payment->transaction_id }}</code></p>
                            </div>
                        @endif
                        @if($booking->payment->receipt_image)
                            <div class="col-md-12 mb-3">
                                <hr>
                                <label class="text-muted small fw-bold">
                                    <i class="fas fa-image text-primary"></i> Ảnh biên lai:
                                </label>
                                <div class="mt-2 p-3 bg-light rounded border">
                                    <div class="text-center">
                                        @php
                                            $imagePath = 'storage/' . $booking->payment->receipt_image;
                                            $imageUrl = asset($imagePath);
                                        @endphp
                                        <a href="{{ $imageUrl }}" target="_blank" class="d-inline-block">
                                            <img src="{{ $imageUrl }}" 
                                                 alt="Biên lai" 
                                                 class="img-thumbnail shadow-sm" 
                                                 style="max-width: 600px; width: 100%; cursor: pointer; border: 2px solid #dee2e6;"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f8f9fa\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'16\'%3EKhông thể tải ảnh%3C/text%3E%3C/svg%3E';">
                                        </a>
                                        <div class="mt-3">
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-info-circle"></i> 
                                                Click vào ảnh để xem kích thước lớn
                                            </p>
                                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                <a href="{{ $imageUrl }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-expand"></i> Mở ảnh trong tab mới
                                                </a>
                                                <a href="{{ $imageUrl }}" 
                                                   download 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i> Tải ảnh xuống
                                                </a>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Đường dẫn: <code>{{ $booking->payment->receipt_image }}</code>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($booking->payment->payment_method == 'bank_transfer_qr' && $booking->payment->payment_status == 'pending')
                            <div class="col-md-12 mb-3">
                                <hr>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Chưa có ảnh biên lai</strong>
                                    <p class="mb-0 mt-2">Khách hàng chưa upload ảnh biên lai thanh toán. Bạn có thể yêu cầu khách hàng gửi ảnh biên lai để xác nhận thanh toán.</p>
                                </div>
                            </div>
                        @endif
                        @if($booking->payment->notes)
                            <div class="col-md-12 mb-3">
                                <hr>
                                <label class="text-muted small">Ghi chú:</label>
                                <p class="mb-0">{{ nl2br(e($booking->payment->notes)) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-exclamation-triangle"></i> Chưa có thông tin thanh toán
                </div>
                @if($booking->status !== 'cancelled')
                <a href="{{ route('admin.payments.create', ['booking_id' => $booking->id]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-money-bill-wave"></i> Tạo thanh toán
                </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Actions Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hành động</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-edit"></i> Sửa đặt phòng
                </a>
                
                @if(!$booking->payment)
                    <a href="{{ route('admin.payments.create', ['booking_id' => $booking->id]) }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-money-bill-wave"></i> Tạo thanh toán
                    </a>
                @elseif($booking->payment->payment_status != 'completed')
                    {{-- Chỉ hiển thị cập nhật thanh toán cho trường hợp thanh toán tại khách sạn (cash) --}}
                    {{-- Không hiển thị cho thanh toán QR vì đã được xử lý qua trang payment riêng --}}
                    @if($booking->payment->payment_method != 'bank_transfer_qr')
                        <a href="{{ route('admin.payments.create', ['booking_id' => $booking->id]) }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-money-bill-wave"></i> Cập nhật thanh toán
                        </a>
                    @else
                        <a href="{{ route('admin.payments.show', $booking->payment->id) }}" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-eye"></i> Xem chi tiết thanh toán
                        </a>
                    @endif
                @endif

                <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" 
                            onclick="return confirm('Bạn có chắc muốn xóa đặt phòng này?')">
                        <i class="fas fa-trash"></i> Xóa đặt phòng
                    </button>
                </form>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <li class="mb-3">
                        <i class="fas fa-plus-circle text-primary"></i>
                        <strong>Đặt phòng</strong>
                        <br>
                        <small class="text-muted">{{ $booking->created_at->format('d/m/Y H:i') }}</small>
                    </li>
                    
                    @if($booking->payment && $booking->payment->payment_date)
                        <li class="mb-3">
                            <i class="fas fa-credit-card text-success"></i>
                            <strong>Thanh toán</strong>
                            <br>
                            <small class="text-muted">{{ $booking->payment->payment_date->format('d/m/Y H:i') }}</small>
                        </li>
                    @endif

                    @if($booking->status == 'checked_in')
                        <li class="mb-3">
                            <i class="fas fa-sign-in-alt text-info"></i>
                            <strong>Nhận phòng</strong>
                            <br>
                            <small class="text-muted">{{ $booking->check_in_date->format('d/m/Y') }}</small>
                        </li>
                    @endif

                    @if($booking->status == 'checked_out')
                        <li class="mb-3">
                            <i class="fas fa-sign-out-alt text-secondary"></i>
                            <strong>Trả phòng</strong>
                            <br>
                            <small class="text-muted">{{ $booking->check_out_date->format('d/m/Y') }}</small>
                        </li>
                    @endif

                    @if($booking->status == 'cancelled')
                        <li class="mb-3">
                            <i class="fas fa-times-circle text-danger"></i>
                            <strong>Đã hủy</strong>
                            <br>
                            <small class="text-muted">{{ $booking->updated_at->format('d/m/Y H:i') }}</small>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        list-style: none;
        padding-left: 0;
    }
    .timeline li {
        padding-left: 30px;
        position: relative;
    }
    .timeline li i {
        position: absolute;
        left: 0;
        top: 0;
    }
</style>
@endpush
@endsection

