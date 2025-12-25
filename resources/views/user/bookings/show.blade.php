@extends('layouts.app')

@section('title', 'Chi tiết đặt phòng #' . $booking->id)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.bookings.index') }}">Đặt phòng của tôi</a></li>
            <li class="breadcrumb-item active">Chi tiết #{{ $booking->id }}</li>
        </ol>
    </nav>

    <!-- Thông báo hoàn tiền nổi bật -->
    @php
        $refundRequest = null;
        if ($booking->payment) {
            $refundRequest = \App\Models\RefundRequest::where('payment_id', $booking->payment->id)->first();
        }
    @endphp
    
    @if($booking->payment && $booking->payment->payment_status == 'refunded' && $refundRequest)
        @if($refundRequest->status == 'completed')
            {{-- Đã hoàn tiền thành công --}}
            <div class="alert alert-success border-success shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-money-bill-wave me-2"></i>Hoàn tiền thành công!
                        </h5>
                        <p class="mb-2">
                            Yêu cầu hoàn tiền của bạn đã được xử lý thành công.
                        </p>
                        <p class="mb-1">
                            @if($booking->payment->payment_method == 'vnpay')
                                <strong>Tiền sẽ được hoàn về tài khoản VNPay trong vòng 3-5 ngày làm việc.</strong>
                            @elseif($booking->payment->payment_method == 'cash')
                                <strong>Vui lòng liên hệ khách sạn để nhận hoàn tiền.</strong>
                            @else
                                <strong>Tiền sẽ được hoàn về tài khoản trong vòng 3-5 ngày làm việc.</strong>
                            @endif
                        </p>
                        @if($refundRequest->admin_notes)
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-sticky-note me-1"></i>
                                <strong>Ghi chú:</strong> {{ $refundRequest->admin_notes }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($refundRequest->status == 'approved')
            {{-- Đã duyệt, đang chờ hoàn tiền --}}
            <div class="alert alert-info border-info shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle fa-3x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-check-circle me-2"></i>Yêu cầu hoàn tiền đã được duyệt
                        </h5>
                        <p class="mb-2">
                            Yêu cầu hoàn tiền của bạn đã được duyệt. Đang chờ xử lý hoàn tiền.
                        </p>
                        <p class="mb-1">
                            <strong>Số tiền sẽ được hoàn: {{ number_format((float)$refundRequest->refund_amount) }} VNĐ</strong>
                        </p>
                        @if($refundRequest->admin_notes)
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-sticky-note me-1"></i>
                                <strong>Ghi chú:</strong> {{ $refundRequest->admin_notes }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($refundRequest->status == 'pending')
            {{-- Đang chờ xử lý --}}
            <div class="alert alert-warning border-warning shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock fa-3x text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-hourglass-half me-2"></i>Yêu cầu hoàn tiền đang chờ xử lý
                        </h5>
                        <p class="mb-2">
                            Yêu cầu hoàn tiền của bạn đang được xem xét. Vui lòng đợi admin xử lý.
                        </p>
                        <p class="mb-1">
                            <strong>Số tiền yêu cầu hoàn: {{ number_format((float)$refundRequest->refund_amount) }} VNĐ</strong>
                        </p>
                    </div>
                </div>
            </div>
        @elseif($refundRequest->status == 'rejected')
            {{-- Bị từ chối --}}
            <div class="alert alert-danger border-danger shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle fa-3x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-ban me-2"></i>Yêu cầu hoàn tiền bị từ chối
                        </h5>
                        <p class="mb-2">
                            Yêu cầu hoàn tiền của bạn đã bị từ chối.
                        </p>
                        @if($refundRequest->admin_notes)
                            <p class="mb-1">
                                <strong>Lý do:</strong> {{ $refundRequest->admin_notes }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chi tiết đặt phòng #{{ $booking->id }}</h5>
                        @if($booking->status == 'pending')
                            <span class="badge bg-warning fs-6">Chờ xác nhận</span>
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
                    <h6 class="text-muted mb-3">Thông tin phòng</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            @if($booking->room->image)
                            <img src="{{ asset('storage/' . $booking->room->image) }}" 
                                 class="room-image" alt="Phòng {{ $booking->room->room_number }}" width="300" height="200">
                        @else
                            @if ($booking->room->images && count($booking->room->images) > 0 && isset($booking->room->images[0]))
                                <img src="{{ asset('storage/' . $booking->room->images[0]->image_path) }}" width="200" height="200"
                                 class="room-image" alt="Phòng {{ $booking->room->room_number }}">
                            @else
                             <div class="room-placeholder text-white">
                                <i class="fas fa-bed fa-3x opacity-75"></i>
                            </div>
                            @endif
                           
                        @endif
                        </div>
                        <div class="col-md-8">
                            <h5>Phòng {{ $booking->room->room_number }}</h5>
                            <p class="text-muted">{{ $booking->room->room_type }}</p>
                            <p><i class="fas fa-users"></i> Sức chứa: {{ $booking->room->capacity }} người</p>
                            <p><i class="fas fa-dollar-sign"></i> Giá: {{ number_format($booking->room->price_per_night) }} VNĐ/đêm</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">Thông tin đặt phòng</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Ngày nhận phòng:</label>
                            <p class="fw-bold mb-0">
                                {{ $booking->check_in_date->format('d/m/Y') }}
                                @if($booking->check_in_time)
                                    <span class="text-muted">({{ substr($booking->check_in_time, 0, 5) }})</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Ngày trả phòng:</label>
                            <p class="fw-bold mb-0">
                                {{ $booking->check_out_date->format('d/m/Y') }}
                                @if($booking->check_out_time)
                                    <span class="text-muted">({{ substr($booking->check_out_time, 0, 5) }})</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
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
                            <p class="fw-bold mb-0">
                                <p class="fw-bold mb-0">
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

                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Số người:</label>
                            <p class="fw-bold mb-0">{{ $booking->number_of_guests }} người</p>
                        </div>
                    </div>

                    @if($booking->special_requests)
                        <div class="mb-3">
                            <label class="text-muted small">Yêu cầu đặc biệt:</label>
                            <p class="mb-0">{{ $booking->special_requests }}</p>
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Tổng tiền:</h5>
                        <h4 class="text-primary mb-0">{{ number_format($booking->total_price) }} VNĐ</h4>
                    </div>

                    <div class="d-flex gap-2">
                        @php
                            $canPay = false;
                            $isAdminReject = false;
                            
                            // Kiểm tra nếu booking đã bị hủy
                            if ($booking->status === 'cancelled') {
                                $isAdminReject = true;
                            }
                            // Kiểm tra nếu payment đã bị admin từ chối
                            elseif ($booking->payment && $booking->payment->payment_status === 'failed') {
                                $notes = $booking->payment->notes ?? '';
                                if (str_contains($notes, '[ADMIN]')) {
                                    $isAdminReject = true;
                                }
                            }
                            
                            // Chỉ cho phép thanh toán nếu booking status là pending và không bị admin từ chối
                            if ($booking->status == 'pending' && !$isAdminReject) {
                                if (!$booking->payment) {
                                    $canPay = true;
                                } elseif ($booking->payment->payment_status === 'pending') {
                                    $canPay = true;
                                } elseif ($booking->payment->payment_status === 'failed') {
                                    // Chỉ cho phép thanh toán lại nếu không phải admin từ chối
                                    $notes = $booking->payment->notes ?? '';
                                    if (!str_contains($notes, '[ADMIN]')) {
                                        $canPay = true;
                                    }
                                }
                            }
                        @endphp
                        @if($canPay)
                            <a href="{{ route('user.payments.create', $booking->id) }}" class="btn btn-success">
                                <i class="fas fa-credit-card"></i> 
                                {{ $booking->payment && $booking->payment->payment_status === 'failed' ? 'Thanh toán lại' : 'Thanh toán ngay' }}
                            </a>
                        @elseif($isAdminReject)
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-ban"></i> 
                                <strong>Không thể thanh toán!</strong> 
                                @if($booking->status === 'cancelled')
                                    Đặt phòng đã bị hủy.
                                @else
                                    Thanh toán đã bị admin từ chối.
                                @endif
                            </div>
                        @endif

                        @if(in_array($booking->status, ['pending', 'confirmed']))
                            @php
                                $checkInDate = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
                                $today = \Carbon\Carbon::today()->startOfDay();
                                $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
                                $canCancel = $daysUntilCheckIn >= 1;
                                
                                $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
                                $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
                                
                                $cancellationFee = 0;
                                $refundAmount = 0;
                                if ($booking->payment && $booking->payment->payment_status === 'completed' && $canCancel) {
                                    $totalPaid = $booking->payment->amount;
                                    if ($daysUntilCheckIn >= $cancellationDaysForFullRefund) {
                                        $refundAmount = $totalPaid;
                                    } else {
                                        $cancellationFee = $totalPaid * ($cancellationFeePercentage / 100);
                                        $refundAmount = $totalPaid - $cancellationFee;
                                    }
                                }
                                
                                $cancelMessage = 'Bạn có chắc muốn hủy đặt phòng này?';
                                if ($booking->payment && $booking->payment->payment_status === 'completed' && $canCancel) {
                                    $cancelMessage .= '\n\n⚠️ LƯU Ý: Bạn đã thanh toán, vui lòng nhập thông tin tài khoản/QR Code để nhận hoàn tiền.';
                                    if ($cancellationFee > 0) {
                                        $cancelMessage .= '\n\nPhí hủy phòng: ' . number_format($cancellationFee) . ' VNĐ (' . $cancellationFeePercentage . '% tổng tiền)';
                                        $cancelMessage .= '\nSố tiền được hoàn: ' . number_format($refundAmount) . ' VNĐ';
                                    } else {
                                        $cancelMessage .= '\n\nBạn sẽ được hoàn tiền đầy đủ: ' . number_format($refundAmount) . ' VNĐ';
                                    }
                                    $cancelMessage .= '\n\nSau khi xác nhận, bạn sẽ được chuyển đến trang nhập thông tin hoàn tiền.';
                                }
                            @endphp
                            
                            @if($canCancel)
                                @if($booking->payment && $booking->payment->payment_status === 'completed')
                                    {{-- Nếu đã thanh toán, mở modal nhập thông tin hoàn tiền --}}
                                    <button type="button" class="btn btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#cancelBookingModal">
                                        <i class="fas fa-times"></i> Hủy đặt phòng
                                    </button>
                                @else
                                    {{-- Nếu chưa thanh toán, hủy trực tiếp --}}
                                    <form action="{{ route('user.bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('{{ $cancelMessage }}')">
                                            <i class="fas fa-times"></i> Hủy đặt phòng
                                        </button>
                                    </form>
                                @endif
                                @if($booking->payment && $booking->payment->payment_status === 'completed')
                                    <div class="alert alert-info mt-3 border-start border-info border-4">
                                        <h6 class="alert-heading mb-2">
                                            <i class="fas fa-info-circle text-info"></i> 
                                            <strong>Chính sách hủy phòng và hoàn tiền:</strong>
                                        </h6>
                                        <div class="mb-2">
                                            @if($daysUntilCheckIn >= $cancellationDaysForFullRefund)
                                                <div class="d-flex align-items-start mb-2">
                                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                    <div>
                                                        <strong class="text-success">Hoàn tiền đầy đủ:</strong>
                                                        <p class="mb-0">
                                                            Bạn đang hủy trước <strong>{{ $daysUntilCheckIn }} ngày</strong> so với ngày check-in 
                                                            ({{ $booking->check_in_date->format('d/m/Y') }}).
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong class="text-success">Số tiền được hoàn: {{ number_format($refundAmount) }} VNĐ</strong> 
                                                            (100% tổng tiền đã thanh toán).
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-start mb-2">
                                                    <i class="fas fa-exclamation-triangle text-warning me-2 mt-1"></i>
                                                    <div>
                                                        <strong class="text-warning">Phí hủy phòng:</strong>
                                                        <p class="mb-0">
                                                            Bạn đang hủy trước <strong>{{ $daysUntilCheckIn }} ngày</strong> so với ngày check-in 
                                                            ({{ $booking->check_in_date->format('d/m/Y') }}).
                                                        </p>
                                                        <p class="mb-0">
                                                            Theo chính sách, hủy phòng trước <strong>{{ $cancellationDaysForFullRefund }} ngày</strong> 
                                                            mới được hoàn tiền đầy đủ.
                                                        </p>
                                                        <p class="mb-0 mt-2">
                                                            <strong class="text-danger">Phí hủy phòng: {{ number_format($cancellationFee) }} VNĐ</strong> 
                                                            ({{ $cancellationFeePercentage }}% tổng tiền).
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong class="text-success">Số tiền được hoàn: {{ number_format($refundAmount) }} VNĐ</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <hr class="my-2">
                                        <div class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <strong>Thời gian hoàn tiền:</strong>
                                            @if($booking->payment->payment_method === 'vnpay')
                                                Tiền sẽ được hoàn về tài khoản VNPay trong vòng <strong>3-5 ngày làm việc</strong>.
                                            @elseif($booking->payment->payment_method === 'cash')
                                                Vui lòng <strong>liên hệ khách sạn</strong> để nhận hoàn tiền.
                                            @else
                                                Tiền sẽ được hoàn về tài khoản trong vòng <strong>3-5 ngày làm việc</strong>.
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mt-2">
                                        <small>
                                            <i class="fas fa-info-circle"></i> 
                                            <strong>Lưu ý:</strong> Bạn chưa thanh toán, nên không có phí hủy phòng.
                                        </small>
                                    </div>
                                @endif
                            @else
                                <button type="button" class="btn btn-danger" disabled 
                                        title="Chỉ có thể hủy đặt phòng trước 1 ngày so với ngày nhận phòng">
                                    <i class="fas fa-times"></i> Không thể hủy
                                </button>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Chỉ có thể hủy trước 1 ngày so với ngày nhận phòng
                                </small>
                            @endif
                        @endif

                        @if($booking->status == 'checked_out')
                            @php
                                $hasReview = $booking->review;
                            @endphp
                            @if(!$hasReview)
                                <a href="{{ route('user.reviews.create', ['room_id' => $booking->room_id, 'booking_id' => $booking->id]) }}" class="btn btn-warning">
                                    <i class="fas fa-star"></i> Đánh giá phòng
                                </a>
                            @else
                                <a href="{{ route('user.reviews.index') }}" class="btn btn-info">
                                    <i class="fas fa-star"></i> Xem đánh giá của tôi
                                </a>
                            @endif
                        @endif

                        <a href="{{ route('user.bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($booking->payment)
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="text-muted small">Trạng thái:</label>
                            @if($booking->payment->payment_status == 'completed')
                                <p class="text-success fw-bold mb-0">
                                    <i class="fas fa-check-circle"></i> Đã thanh toán
                                </p>
                            @elseif($booking->payment->payment_status == 'pending')
                                <p class="text-warning fw-bold mb-0">
                                    <i class="fas fa-clock"></i> Chờ xử lý
                                </p>
                            @elseif($booking->payment->payment_status == 'failed')
                                <p class="text-danger fw-bold mb-0">
                                    <i class="fas fa-times-circle"></i> Thất bại
                                </p>
                                @php
                                    $notes = $booking->payment->notes ?? '';
                                    $rejectReason = '';
                                    $isAdminReject = false;
                                    if (str_contains($notes, '[ADMIN]')) {
                                        $isAdminReject = true;
                                        $parts = explode('[ADMIN]', $notes);
                                        if (count($parts) > 1) {
                                            $adminNote = $parts[1];
                                            if (preg_match('/Lý do:\s*(.+?)(?:\n|$)/', $adminNote, $matches)) {
                                                $rejectReason = trim($matches[1]);
                                            }
                                        }
                                    }
                                @endphp
                                @if($isAdminReject && $rejectReason)
                                    <div class="alert alert-danger mt-2 mb-0 small">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Thanh toán đã bị từ chối bởi admin:</strong><br>
                                        {{ $rejectReason }}
                                    </div>
                                @endif
                            @else
                                <p class="text-secondary fw-bold mb-0">
                                    <i class="fas fa-undo"></i> Đã hoàn tiền
                                </p>
                            @endif
                        </div>

                        <div class="mb-2">
                            <label class="text-muted small">Phương thức:</label>
                            <p class="mb-0">
                                @if($booking->payment->payment_method == 'cash')
                                    Tiền mặt
                                @elseif($booking->payment->payment_method == 'credit_card')
                                    Thẻ tín dụng
                                @elseif($booking->payment->payment_method == 'bank_transfer')
                                    Chuyển khoản
                                @elseif($booking->payment->payment_method == 'bank_transfer_qr')
                                    <i class="fas fa-qrcode"></i> QR Chuyển khoản
                                @elseif($booking->payment->payment_method == 'momo')
                                    MoMo
                                @elseif($booking->payment->payment_method == 'vnpay')
                                    VNPay
                                @else
                                    {{ $booking->payment->payment_method }}
                                @endif
                            </p>
                        </div>

                        <div class="mb-2">
                            <label class="text-muted small">Số tiền:</label>
                            <p class="fw-bold mb-0">{{ number_format($booking->payment->amount) }} VNĐ</p>
                        </div>

                        @if($booking->payment->payment_date)
                            <div class="mb-2">
                                <label class="text-muted small">Ngày thanh toán:</label>
                                <p class="mb-0">{{ $booking->payment->payment_date->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif

                        @if($booking->payment->transaction_id)
                            <div class="mb-2">
                                <label class="text-muted small">Mã giao dịch:</label>
                                <p class="mb-0"><code>{{ $booking->payment->transaction_id }}</code></p>
                            </div>
                        @endif

                        @if($booking->payment->notes)
                            <div class="mb-2">
                                <label class="text-muted small">Ghi chú:</label>
                                <p class="mb-0 small">{{ $booking->payment->notes }}</p>
                            </div>
                        @endif

                        @if($booking->payment->payment_status == 'refunded')
                            @php
                                $refundRequestDetail = \App\Models\RefundRequest::where('payment_id', $booking->payment->id)->latest()->first();
                            @endphp
                            @if($refundRequestDetail && $refundRequestDetail->status == 'completed')
                                {{-- Đã hoàn tiền thành công --}}
                                <div class="alert alert-success border-success mt-3 mb-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                                        <div>
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-money-bill-wave me-2"></i>Đã hoàn tiền thành công!
                                            </h6>
                                            <p class="mb-1">
                                                @if($booking->payment->payment_method == 'vnpay')
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Tiền đã được hoàn về tài khoản VNPay của bạn. 
                                                    <strong>Thời gian nhận tiền: 3-5 ngày làm việc.</strong>
                                                @elseif($booking->payment->payment_method == 'cash')
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Vui lòng liên hệ khách sạn để nhận hoàn tiền.
                                                @else
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Tiền đã được hoàn về tài khoản của bạn. 
                                                    <strong>Thời gian nhận tiền: 3-5 ngày làm việc.</strong>
                                                @endif
                                            </p>
                                            @if($refundRequestDetail->admin_notes)
                                                <small class="text-muted d-block mt-2">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    <strong>Ghi chú:</strong> {{ $refundRequestDetail->admin_notes }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($refundRequestDetail && $refundRequestDetail->status == 'approved')
                                {{-- Đã duyệt, đang chờ hoàn tiền --}}
                                <div class="alert alert-info border-info mt-3 mb-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                                        <div>
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-check-circle me-2"></i>Yêu cầu hoàn tiền đã được duyệt
                                            </h6>
                                            <p class="mb-1">
                                                Yêu cầu hoàn tiền của bạn đã được duyệt. Đang chờ xử lý hoàn tiền.
                                            </p>
                                            <p class="mb-1">
                                                <strong>Số tiền sẽ được hoàn: {{ number_format((float)$refundRequestDetail->refund_amount) }} VNĐ</strong>
                                            </p>
                                            @if($refundRequestDetail->admin_notes)
                                                <small class="text-muted d-block mt-2">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    <strong>Ghi chú:</strong> {{ $refundRequestDetail->admin_notes }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($refundRequestDetail && $refundRequestDetail->status == 'pending')
                                {{-- Đang chờ xử lý --}}
                                <div class="alert alert-warning border-warning mt-3 mb-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock fa-2x me-3 text-warning"></i>
                                        <div>
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-hourglass-half me-2"></i>Yêu cầu hoàn tiền đang chờ xử lý
                                            </h6>
                                            <p class="mb-1">
                                                Yêu cầu hoàn tiền của bạn đang được xem xét. Vui lòng đợi admin xử lý.
                                            </p>
                                            <p class="mb-1">
                                                <strong>Số tiền yêu cầu hoàn: {{ number_format((float)$refundRequestDetail->refund_amount) }} VNĐ</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($refundRequestDetail && $refundRequestDetail->status == 'rejected')
                                {{-- Bị từ chối --}}
                                <div class="alert alert-danger border-danger mt-3 mb-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-times-circle fa-2x me-3 text-danger"></i>
                                        <div>
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-ban me-2"></i>Yêu cầu hoàn tiền bị từ chối
                                            </h6>
                                            <p class="mb-1">
                                                Yêu cầu hoàn tiền của bạn đã bị từ chối.
                                            </p>
                                            @if($refundRequestDetail->admin_notes)
                                                <p class="mb-1">
                                                    <strong>Lý do:</strong> {{ $refundRequestDetail->admin_notes }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($booking->status !== 'cancelled')
                        <hr class="my-3">
                        <div class="d-grid">
                            <a href="{{ route('user.payments.show', $booking->payment->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> Xem chi tiết thanh toán
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Lưu ý</h6>
                    <ul class="small text-muted mb-0">
                        <li>Vui lòng đến nhận phòng đúng giờ</li>
                        <li>Mang theo CCCD/CMND khi nhận phòng</li>
                        <li>Liên hệ khách sạn nếu có thay đổi</li>
                        @php
                            $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
                            $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
                        @endphp
                        <li>
                            <strong>Chính sách hủy phòng và hoàn tiền:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    Hủy trước <strong>{{ $cancellationDaysForFullRefund }} ngày</strong> so với ngày check-in: 
                                    <strong class="text-success">Hoàn tiền đầy đủ 100%</strong>
                                </li>
                                <li>
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Hủy trước <strong>1 ngày</strong> nhưng không đủ {{ $cancellationDaysForFullRefund }} ngày: 
                                    <strong class="text-warning">Phí hủy {{ $cancellationFeePercentage }}%</strong>, hoàn tiền phần còn lại
                                </li>
                                <li>
                                    <i class="fas fa-times-circle text-danger me-1"></i>
                                    Hủy trong vòng <strong>24 giờ</strong> trước check-in: 
                                    <strong class="text-danger">Không được hủy</strong>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal nhập thông tin hoàn tiền khi hủy phòng --}}
@php
    // Đảm bảo $canCancel được định nghĩa
    if (!isset($canCancel)) {
        $checkInDate = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
        $today = \Carbon\Carbon::today()->startOfDay();
        $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
        $canCancel = $daysUntilCheckIn >= 1 && in_array($booking->status, ['pending', 'confirmed']);
    }
@endphp
@if(isset($canCancel) && $canCancel && $booking->payment && $booking->payment->payment_status === 'completed')
    @php
        $checkInDate = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
        $today = \Carbon\Carbon::today()->startOfDay();
        $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
        $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
        $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
        
        $cancellationFee = 0;
        $refundAmount = $booking->payment->amount;
        if ($daysUntilCheckIn < $cancellationDaysForFullRefund && $daysUntilCheckIn >= 1) {
            $cancellationFee = $booking->payment->amount * ($cancellationFeePercentage / 100);
            $refundAmount = $booking->payment->amount - $cancellationFee;
        }
    @endphp
    
    <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelBookingModalLabel">
                        <i class="fas fa-times-circle"></i> Hủy đặt phòng và yêu cầu hoàn tiền
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.refunds.store', $booking->payment->id) }}" method="POST" id="refundForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Lưu ý quan trọng:</strong>
                            <p class="mb-0 mt-2">
                                Sau khi nhập thông tin hoàn tiền và gửi yêu cầu, đặt phòng sẽ được <strong>tự động hủy</strong>.
                            </p>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Thông tin thanh toán:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Số tiền: <strong>{{ number_format($booking->payment->amount) }} VNĐ</strong></li>
                                <li>Mã thanh toán: <strong>#{{ $booking->payment->id }}</strong></li>
                                <li>Mã đặt phòng: <strong>#{{ $booking->id }}</strong></li>
                            </ul>
                        </div>

                        @if($cancellationFee > 0)
                            <div class="alert alert-warning">
                                <strong>Phí hủy phòng:</strong> {{ number_format($cancellationFee) }} VNĐ ({{ $cancellationFeePercentage }}%)
                                <br>
                                <strong>Số tiền được hoàn:</strong> {{ number_format($refundAmount) }} VNĐ
                            </div>
                        @else
                            <div class="alert alert-success">
                                <strong>Số tiền được hoàn:</strong> {{ number_format($refundAmount) }} VNĐ (100%)
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Phương thức hoàn tiền *</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="refund_method" id="refund_bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="refund_bank_transfer">
                                    <i class="fas fa-university"></i> Chuyển khoản ngân hàng
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refund_method" id="refund_qr_code" value="qr_code">
                                <label class="form-check-label" for="refund_qr_code">
                                    <i class="fas fa-qrcode"></i> Mã QR Code
                                </label>
                            </div>
                        </div>

                        {{-- Thông tin chuyển khoản ngân hàng --}}
                        <div id="bankTransferFields">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Tên ngân hàng *</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                       placeholder="Ví dụ: Vietcombank, BIDV, Techcombank..." required>
                            </div>
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Số tài khoản *</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" 
                                       placeholder="Nhập số tài khoản" required>
                            </div>
                            <div class="mb-3">
                                <label for="account_holder_name" class="form-label">Tên chủ tài khoản *</label>
                                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                                       placeholder="Nhập tên chủ tài khoản" required>
                            </div>
                        </div>

                        {{-- Thông tin QR Code --}}
                        <div id="qrCodeFields" style="display: none;">
                            <div class="mb-3">
                                <label for="qr_code_image" class="form-label">Upload ảnh QR Code *</label>
                                <input type="file" class="form-control" id="qr_code_image" name="qr_code_image" 
                                       accept="image/*" onchange="previewQRImage(this)">
                                <small class="form-text text-muted">
                                    Chọn ảnh QR Code của bạn (JPG, PNG, GIF - tối đa 5MB)
                                </small>
                                <div id="qr_code_preview" class="mt-3" style="display: none;">
                                    <img id="qr_code_preview_img" src="" alt="QR Code Preview" 
                                         class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeQRImage()">
                                            <i class="fas fa-times"></i> Xóa ảnh
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="qr_code" class="form-label">Hoặc nhập thông tin nhận tiền (tùy chọn)</label>
                                <textarea class="form-control" id="qr_code" name="qr_code" rows="3" 
                                          placeholder="Nhập thông tin tài khoản hoặc ghi chú nếu có..."></textarea>
                                <small class="form-text text-muted">
                                    Nếu không có ảnh QR Code, bạn có thể nhập thông tin tài khoản ở đây
                                </small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="refund_notes" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea class="form-control" id="refund_notes" name="notes" rows="3" 
                                      placeholder="Nhập ghi chú nếu có..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Đóng
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check"></i> Xác nhận hủy và gửi yêu cầu hoàn tiền
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const refundMethodRadios = document.querySelectorAll('input[name="refund_method"]');
            const bankTransferFields = document.getElementById('bankTransferFields');
            const qrCodeFields = document.getElementById('qrCodeFields');
            const bankNameInput = document.getElementById('bank_name');
            const accountNumberInput = document.getElementById('account_number');
            const accountHolderNameInput = document.getElementById('account_holder_name');
            const qrCodeInput = document.getElementById('qr_code');

            refundMethodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'bank_transfer') {
                        bankTransferFields.style.display = 'block';
                        qrCodeFields.style.display = 'none';
                        bankNameInput.required = true;
                        accountNumberInput.required = true;
                        accountHolderNameInput.required = true;
                        qrCodeInput.required = false;
                    } else {
                        bankTransferFields.style.display = 'none';
                        qrCodeFields.style.display = 'block';
                        bankNameInput.required = false;
                        accountNumberInput.required = false;
                        accountHolderNameInput.required = false;
                        qrCodeInput.required = true;
                    }
                });
            });

            // Validate form
            document.getElementById('refundForm').addEventListener('submit', function(e) {
                const selectedMethod = document.querySelector('input[name="refund_method"]:checked').value;
                
                if (selectedMethod === 'bank_transfer') {
                    if (!bankNameInput.value || !accountNumberInput.value || !accountHolderNameInput.value) {
                        e.preventDefault();
                        alert('Vui lòng điền đầy đủ thông tin chuyển khoản ngân hàng!');
                        return false;
                    }
                } else {
                    const qrCodeImageInput = document.getElementById('qr_code_image');
                    if (!qrCodeImageInput.files || !qrCodeImageInput.files[0]) {
                        if (!qrCodeInput.value || qrCodeInput.value.trim() === '') {
                            e.preventDefault();
                            alert('Vui lòng upload ảnh QR Code hoặc nhập thông tin nhận tiền!');
                            return false;
                        }
                    }
                }
            });
        });
    </script>
    @endpush
@endif
@endsection

