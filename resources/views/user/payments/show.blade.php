@extends('layouts.app')

@section('title', 'Chi tiết thanh toán #' . $payment->id)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.bookings.index') }}">Đặt phòng của tôi</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.bookings.show', $payment->booking_id) }}">Đặt phòng #{{ $payment->booking_id }}</a></li>
            <li class="breadcrumb-item active">Thanh toán #{{ $payment->id }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <!-- Payment Info Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Thông tin thanh toán #{{ $payment->id }}</h5>
                        @if($payment->payment_status == 'completed')
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Hoàn thành</span>
                        @elseif($payment->payment_status == 'pending')
                            <span class="badge bg-warning fs-6"><i class="fas fa-clock"></i> Chờ xử lý</span>
                        @elseif($payment->payment_status == 'failed')
                            <span class="badge bg-danger fs-6"><i class="fas fa-times-circle"></i> Thất bại</span>
                        @else
                            <span class="badge bg-secondary fs-6"><i class="fas fa-undo"></i> Đã hoàn tiền</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Mã thanh toán:</label>
                            <p class="fw-bold">#{{ $payment->id }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Ngày tạo:</label>
                            <p class="fw-bold">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Số tiền:</label>
                            <p class="fw-bold text-primary fs-4">{{ number_format($payment->amount) }} VNĐ</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Phương thức thanh toán:</label>
                            <p class="fw-bold mb-0">
                                @if($payment->payment_method == 'cash')
                                    <i class="fas fa-money-bill-wave text-success"></i> Tiền mặt
                                @elseif($payment->payment_method == 'credit_card')
                                    <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng
                                @elseif($payment->payment_method == 'bank_transfer')
                                    <i class="fas fa-university text-info"></i> Chuyển khoản ngân hàng
                                @elseif($payment->payment_method == 'bank_transfer_qr')
                                    <i class="fas fa-qrcode text-info"></i> QR Chuyển khoản
                                @elseif($payment->payment_method == 'momo')
                                    <i class="fas fa-mobile-alt text-warning"></i> MoMo
                                @elseif($payment->payment_method == 'vnpay')
                                    <i class="fas fa-wallet text-danger"></i> VNPay
                                @else
                                    {{ $payment->payment_method }}
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($payment->payment_date)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Ngày thanh toán:</label>
                                <p class="fw-bold">{{ $payment->payment_date->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($payment->transaction_id)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="text-muted small">Mã giao dịch:</label>
                                <p class="fw-bold"><code class="bg-light p-2 rounded">{{ $payment->transaction_id }}</code></p>
                            </div>
                        </div>
                    @endif

                    @if($payment->receipt_image)
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Ảnh biên lai:</label>
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $payment->receipt_image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $payment->receipt_image) }}" alt="Biên lai" class="img-thumbnail" style="max-width: 300px; cursor: pointer;">
                                </a>
                                <p class="text-muted small mt-2">
                                    <i class="fas fa-info-circle"></i> Click vào ảnh để xem kích thước lớn
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($payment->notes)
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">Ghi chú:</label>
                            <p class="mb-0">{{ $payment->notes }}</p>
                        </div>
                    @endif

                    @if($payment->payment_status === 'pending' && $payment->payment_method === 'bank_transfer_qr')
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Thanh toán đang chờ xử lý!</strong> 
                            Bạn đã gửi xác nhận thanh toán. Admin sẽ kiểm tra và xác nhận trong thời gian sớm nhất.
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('user.payments.qr', $payment->id) }}" class="btn btn-primary">
                                <i class="fas fa-qrcode"></i> Xem lại QR Code
                            </a>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPaymentModal">
                                <i class="fas fa-times"></i> Hủy thanh toán QR
                            </button>
                        </div>
                    @elseif($payment->payment_status === 'failed' && $payment->payment_method === 'bank_transfer_qr')
                        <hr>
                        @php
                            $notes = $payment->notes ?? '';
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
                        <div class="alert alert-{{ $isAdminReject ? 'danger' : 'warning' }}">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Thanh toán đã bị {{ $isAdminReject ? 'từ chối bởi admin' : 'hủy' }}!</strong>
                            @if($isAdminReject && $rejectReason)
                                <p class="mb-2 mt-2">
                                    <strong>Lý do từ chối:</strong> {{ $rejectReason }}
                                </p>
                            @endif
                            @if($isAdminReject)
                                <p class="mb-0"><strong>Không thể thanh toán lại.</strong> Đặt phòng đã bị hủy.</p>
                            @else
                                <p class="mb-0">Bạn có thể chọn phương thức thanh toán khác để thanh toán lại.</p>
                            @endif
                        </div>
                        @if(!$isAdminReject)
                            <div class="d-grid gap-2">
                                <a href="{{ route('user.payments.create', $payment->booking_id) }}" class="btn btn-success">
                                    <i class="fas fa-credit-card"></i> Thanh toán lại
                                </a>
                            </div>
                        @endif
                    @elseif($payment->payment_status === 'completed')
                        {{-- Thanh toán đã hoàn thành - Cho phép hủy đặt phòng nếu chưa checkout --}}
                        @php
                            $canCancelBooking = in_array($payment->booking->status, ['pending', 'confirmed']);
                            $checkInDate = \Carbon\Carbon::parse($payment->booking->check_in_date)->startOfDay();
                            $today = \Carbon\Carbon::today()->startOfDay();
                            $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
                            $canCancel = $daysUntilCheckIn >= 1;
                            
                            $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
                            $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
                            
                            $cancellationFee = 0;
                            $refundAmount = 0;
                            if ($canCancel && $canCancelBooking) {
                                $totalPaid = $payment->amount;
                                if ($daysUntilCheckIn >= $cancellationDaysForFullRefund) {
                                    $refundAmount = $totalPaid;
                                } else {
                                    $cancellationFee = $totalPaid * ($cancellationFeePercentage / 100);
                                    $refundAmount = $totalPaid - $cancellationFee;
                                }
                            }
                            
                            $cancelMessage = 'Bạn có chắc muốn hủy đặt phòng này?';
                            if ($canCancel && $canCancelBooking) {
                                if ($cancellationFee > 0) {
                                    $cancelMessage .= '\n\nPhí hủy phòng: ' . number_format($cancellationFee) . ' VNĐ (' . $cancellationFeePercentage . '% tổng tiền)';
                                    $cancelMessage .= '\nSố tiền được hoàn: ' . number_format($refundAmount) . ' VNĐ';
                                } else {
                                    $cancelMessage .= '\n\nBạn sẽ được hoàn tiền đầy đủ: ' . number_format($refundAmount) . ' VNĐ';
                                }
                            }
                        @endphp
                        @if($canCancelBooking)
                            <hr>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Thanh toán đã hoàn thành!</strong>
                                @if($canCancel)
                                    <p class="mb-2 mt-2">
                                        Bạn có thể hủy đặt phòng từ đây. Hệ thống sẽ tự động tính phí hủy và hoàn tiền theo chính sách.
                                    </p>
                                    @if($daysUntilCheckIn >= $cancellationDaysForFullRefund)
                                        <p class="mb-0 small">
                                            <strong class="text-success">✓ Hoàn tiền đầy đủ:</strong> 
                                            Bạn đang hủy trước <strong>{{ $daysUntilCheckIn }} ngày</strong> so với ngày check-in. 
                                            Số tiền được hoàn: <strong>{{ number_format($refundAmount) }} VNĐ</strong> (100% tổng tiền).
                                        </p>
                                    @else
                                        <p class="mb-0 small">
                                            <strong class="text-warning">⚠ Phí hủy phòng:</strong> 
                                            Bạn đang hủy trước <strong>{{ $daysUntilCheckIn }} ngày</strong> so với ngày check-in. 
                                            Phí hủy: <strong>{{ number_format($cancellationFee) }} VNĐ</strong> ({{ $cancellationFeePercentage }}%). 
                                            Số tiền được hoàn: <strong>{{ number_format($refundAmount) }} VNĐ</strong>.
                                        </p>
                                    @endif
                                @else
                                    <p class="mb-2 mt-2">
                                        <strong class="text-danger">Không thể hủy đặt phòng</strong> vì đã quá gần ngày check-in.
                                    </p>
                                    <p class="mb-0 small">
                                        Chỉ có thể hủy trước <strong>1 ngày</strong> so với ngày nhận phòng 
                                        ({{ $payment->booking->check_in_date->format('d/m/Y') }}).
                                    </p>
                                @endif
                            </div>
                            @if($canCancel)
                                <div class="d-grid gap-2">
                                    @if($payment->payment_status === 'completed')
                                        {{-- Nếu đã thanh toán, mở modal nhập thông tin hoàn tiền --}}
                                        <button type="button" class="btn btn-danger w-100" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelBookingModal">
                                            <i class="fas fa-times-circle"></i> Hủy đặt phòng
                                        </button>
                                    @else
                                        {{-- Nếu chưa thanh toán, hủy trực tiếp --}}
                                        <form action="{{ route('user.bookings.cancel', $payment->booking_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger w-100" 
                                                    onclick="return confirm('{{ $cancelMessage }}')">
                                                <i class="fas fa-times-circle"></i> Hủy đặt phòng
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-danger w-100" disabled>
                                        <i class="fas fa-times"></i> Không thể hủy
                                    </button>
                                </div>
                            @endif
                        @else
                            <hr>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Thanh toán đã hoàn thành!</strong>
                                <p class="mb-0 mt-2">
                                    Đặt phòng đã được xử lý. 
                                    @if($payment->booking->status === 'completed')
                                        Khách hàng đã checkout.
                                    @elseif($payment->booking->status === 'checked_in')
                                        Khách hàng đã check-in.
                                    @elseif($payment->booking->status === 'cancelled')
                                        Đặt phòng đã bị hủy.
                                    @endif
                                </p>
                            </div>
                            
                            {{-- Nút yêu cầu hoàn tiền nếu booking đã cancelled --}}
                            @if($payment->booking->status === 'cancelled')
                                @php
                                    $hasRefundRequest = \App\Models\RefundRequest::where('payment_id', $payment->id)
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->exists();
                                @endphp
                                @if(!$hasRefundRequest)
                                    <div class="d-grid gap-2 mt-3">
                                        <a href="{{ route('user.refunds.create', $payment->id) }}" class="btn btn-warning">
                                            <i class="fas fa-money-bill-wave"></i> Yêu cầu hoàn tiền
                                        </a>
                                        <small class="text-muted text-center">
                                            Vui lòng nhập thông tin tài khoản ngân hàng hoặc mã QR để nhận hoàn tiền
                                        </small>
                                    </div>
                                @else
                                    @php
                                        $refundRequest = \App\Models\RefundRequest::where('payment_id', $payment->id)
                                            ->latest()
                                            ->first();
                                    @endphp
                                    @if($refundRequest)
                                        @if($refundRequest->status === 'completed')
                                            {{-- Đã hoàn tiền thành công --}}
                                            <div class="alert alert-success mt-3">
                                                <i class="fas fa-check-circle"></i> 
                                                <strong>Đã hoàn tiền thành công!</strong>
                                                <p class="mb-1 mt-2">
                                                    Yêu cầu hoàn tiền của bạn đã được xử lý thành công.
                                                </p>
                                                <p class="mb-1">
                                                    Số tiền đã hoàn: <strong>{{ number_format((float)$refundRequest->refund_amount) }} VNĐ</strong>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Thời gian nhận tiền: 3-5 ngày làm việc.</strong>
                                                </p>
                                                @if($refundRequest->admin_notes)
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        <strong>Ghi chú:</strong> {{ $refundRequest->admin_notes }}
                                                    </small>
                                                @endif
                                            </div>
                                        @elseif($refundRequest->status === 'approved')
                                            {{-- Đã duyệt, đang chờ hoàn tiền --}}
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-check-circle"></i> 
                                                <strong>Yêu cầu hoàn tiền đã được duyệt</strong>
                                                <p class="mb-1 mt-2">
                                                    Yêu cầu hoàn tiền của bạn đã được duyệt. Đang chờ xử lý hoàn tiền.
                                                </p>
                                                <p class="mb-1">
                                                    Số tiền sẽ được hoàn: <strong>{{ number_format((float)$refundRequest->refund_amount) }} VNĐ</strong>
                                                </p>
                                                @if($refundRequest->admin_notes)
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        <strong>Ghi chú:</strong> {{ $refundRequest->admin_notes }}
                                                    </small>
                                                @endif
                                            </div>
                                        @elseif($refundRequest->status === 'pending')
                                            {{-- Đang chờ xử lý --}}
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-clock"></i> 
                                                <strong>Yêu cầu hoàn tiền đang chờ xử lý</strong>
                                                <p class="mb-1 mt-2">
                                                    Yêu cầu hoàn tiền của bạn đang được xem xét. Vui lòng đợi admin xử lý.
                                                </p>
                                                <p class="mb-1">
                                                    Số tiền yêu cầu hoàn: <strong>{{ number_format((float)$refundRequest->refund_amount) }} VNĐ</strong>
                                                </p>
                                            </div>
                                        @elseif($refundRequest->status === 'rejected')
                                            {{-- Bị từ chối --}}
                                            <div class="alert alert-danger mt-3">
                                                <i class="fas fa-times-circle"></i> 
                                                <strong>Yêu cầu hoàn tiền bị từ chối</strong>
                                                <p class="mb-1 mt-2">
                                                    Yêu cầu hoàn tiền của bạn đã bị từ chối.
                                                </p>
                                                @if($refundRequest->admin_notes)
                                                    <p class="mb-1">
                                                        <strong>Lý do:</strong> {{ $refundRequest->admin_notes }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Booking Info Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Thông tin đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Mã đặt phòng:</label>
                        <p class="fw-bold">#{{ $payment->booking->id }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Phòng:</label>
                        <p class="fw-bold mb-0">
                            <i class="fas fa-door-open text-primary"></i> 
                            {{ $payment->booking->room->room_number }} - {{ $payment->booking->room->room_type }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Ngày nhận phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->check_in_date->format('d/m/Y') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Ngày trả phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->check_out_date->format('d/m/Y') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Số đêm:</label>
                        <p class="fw-bold">{{ $payment->booking->check_in_date->diffInDays($payment->booking->check_out_date) }} đêm</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Số người:</label>
                        <p class="fw-bold">{{ $payment->booking->number_of_guests }} người</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Tổng tiền đặt phòng:</label>
                        <p class="fw-bold text-primary fs-5">{{ number_format($payment->booking->total_price) }} VNĐ</p>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="{{ route('user.bookings.show', $payment->booking_id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> Xem chi tiết đặt phòng
                        </a>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        @if($payment->payment_method === 'bank_transfer_qr')
                            <a href="{{ route('user.payments.qr', $payment->id) }}" class="btn btn-info">
                                <i class="fas fa-qrcode"></i> Xem QR Code
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal hủy thanh toán -->
@if($payment->payment_status === 'pending' && $payment->payment_method === 'bank_transfer_qr')
<div class="modal fade" id="cancelPaymentModal" tabindex="-1" aria-labelledby="cancelPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.payments.cancel', $payment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelPaymentModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Xác nhận hủy thanh toán
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(!empty($payment->transaction_id) || !empty($payment->receipt_image))
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Lưu ý:</strong> Bạn đã xác nhận chuyển khoản. Nếu hủy thanh toán, vui lòng nhập lý do.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Bạn có chắc muốn hủy thanh toán QR này? Sau khi hủy, bạn có thể chọn phương thức thanh toán khác.
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">
                            Lý do hủy thanh toán 
                            @if(!empty($payment->transaction_id) || !empty($payment->receipt_image))
                                <span class="text-danger">*</span>
                            @endif
                            <small class="text-muted">(tùy chọn)</small>
                        </label>
                        <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="3" 
                                  placeholder="Nhập lý do hủy thanh toán (nếu có)">{{ old('cancel_reason') }}</textarea>
                        <small class="text-muted">Ví dụ: Đã chuyển nhầm, Muốn đổi phương thức thanh toán, v.v.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check"></i> Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal nhập thông tin hoàn tiền khi hủy phòng --}}
@if($canCancel && $payment->payment_status === 'completed')
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
    
    <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelBookingModalLabel">
                        <i class="fas fa-times-circle"></i> Hủy đặt phòng và yêu cầu hoàn tiền
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.refunds.store', $payment->id) }}" method="POST" id="refundForm" enctype="multipart/form-data">
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
                                <li>Số tiền: <strong>{{ number_format($payment->amount) }} VNĐ</strong></li>
                                <li>Mã thanh toán: <strong>#{{ $payment->id }}</strong></li>
                                <li>Mã đặt phòng: <strong>#{{ $payment->booking->id }}</strong></li>
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
            const refundMethodRadios = document.querySelectorAll('#cancelBookingModal input[name="refund_method"]');
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

            // Preview QR Code image
            window.previewQRImage = function(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('qr_code_preview_img').src = e.target.result;
                        document.getElementById('qr_code_preview').style.display = 'block';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            // Remove QR Code image
            window.removeQRImage = function() {
                document.getElementById('qr_code_image').value = '';
                document.getElementById('qr_code_preview').style.display = 'none';
                document.getElementById('qr_code_preview_img').src = '';
            };

            // Validate form
            document.getElementById('refundForm').addEventListener('submit', function(e) {
                const selectedMethod = document.querySelector('#cancelBookingModal input[name="refund_method"]:checked').value;
                
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

