@extends('layouts.admin')

@section('title', 'Chi tiết Checkout #' . $booking->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-door-open"></i> Chi tiết Checkout #{{ $booking->id }}</h2>
    <a href="{{ route('admin.employee.checkout.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Booking Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin đặt phòng</h5>
                    @if($booking->status == 'pending')
                        <span class="badge bg-warning text-dark fs-6">Chờ xử lý</span>
                    @elseif($booking->status == 'confirmed')
                        <span class="badge bg-success fs-6">Đã xác nhận</span>
                    @elseif($booking->status == 'checked_in')
                        <span class="badge bg-info fs-6">Đã nhận phòng</span>
                    @elseif($booking->status == 'checked_out')
                        <span class="badge bg-secondary fs-6">Đã trả phòng</span>
                    @elseif($booking->status == 'completed')
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle"></i> Hoàn thành
                        </span>
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
                </div>

                <hr>

                <h6 class="text-muted mb-3">Chi tiết đặt phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày nhận phòng:</label>
                        <p class="fw-bold">{{ $booking->check_in_date->format('d/m/Y') }} 
                            @if($booking->check_in_time) {{ $booking->check_in_time }} @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày trả phòng:</label>
                        <p class="fw-bold">{{ $booking->check_out_date->format('d/m/Y') }} 
                            @if($booking->check_out_time) {{ $booking->check_out_time }} @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số khách:</label>
                        <p class="fw-bold">{{ $booking->number_of_guests }} người</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Tổng tiền phòng:</label>
                        <p class="fw-bold text-primary fs-5">
                            @php
                                $originalPrice = $booking->total_price;
                                $additionalChargesTotal = $booking->additionalCharges ? $booking->additionalCharges->sum('total_price') : 0;
                                $originalPrice = $originalPrice - $additionalChargesTotal;
                            @endphp
                            {{ number_format($originalPrice) }} VNĐ
                        </p>
                    </div>
                </div>
                
                @if($booking->additionalCharges && $booking->additionalCharges->count() > 0)
                    <hr>
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-plus-circle text-primary"></i> Phí phát sinh:
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên dịch vụ</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Giá đơn vị</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->additionalCharges as $charge)
                                <tr>
                                    <td><strong>{{ $charge->service_name }}</strong></td>
                                    <td class="text-center">{{ $charge->quantity }}</td>
                                    <td class="text-end">{{ number_format($charge->unit_price) }} VNĐ</td>
                                    <td class="text-end text-success"><strong>{{ number_format($charge->total_price) }} VNĐ</strong></td>
                                    <td><small class="text-muted">{{ $charge->notes ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Tổng phí phát sinh:</th>
                                    <th class="text-end text-success">{{ number_format($booking->additionalCharges->sum('total_price')) }} VNĐ</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Tổng cộng:</th>
                                    <th class="text-end text-danger fs-5">{{ number_format($booking->total_price) }} VNĐ</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    {{-- Hiển thị thông tin thanh toán phí phát sinh --}}
                    @php
                        // Tìm payment phí phát sinh (có transaction_id bắt đầu bằng 'ADD_' hoặc payment thứ 2)
                        $additionalPayment = $booking->payments->filter(function($payment) {
                            return strpos($payment->transaction_id ?? '', 'ADD_') === 0;
                        })->first();
                        
                        // Nếu không tìm thấy bằng transaction_id, lấy payment thứ 2 (nếu có)
                        if (!$additionalPayment && $booking->payments->count() > 1) {
                            $additionalPayment = $booking->payments->sortBy('created_at')->skip(1)->first();
                        }
                    @endphp
                    @if($additionalPayment)
                        <div class="alert alert-info mt-3">
                            <h6 class="mb-2">
                                <i class="fas fa-credit-card"></i> Thanh toán phí phát sinh:
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Số tiền:</small>
                                    <p class="mb-0 fw-bold">{{ number_format($additionalPayment->amount) }} VNĐ</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Phương thức:</small>
                                    <p class="mb-0">
                                        @if($additionalPayment->payment_method == 'cash')
                                            <span class="badge bg-success">Tiền mặt</span>
                                        @else
                                            <span class="badge bg-primary">Chuyển khoản QR</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted">Trạng thái:</small>
                                    <p class="mb-0">
                                        @if($additionalPayment->payment_status == 'completed')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        @elseif($additionalPayment->payment_status == 'pending')
                                            <span class="badge bg-warning">Chờ thanh toán</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $additionalPayment->payment_status }}</span>
                                        @endif
                                    </p>
                                </div>
                                @if($additionalPayment->payment_date)
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted">Ngày thanh toán:</small>
                                    <p class="mb-0">{{ $additionalPayment->payment_date->format('d/m/Y H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Payment Status Card -->
        <div class="card mb-4 {{ $booking->payment && $booking->payment->payment_status == 'completed' ? 'border-success' : 'border-danger' }}">
            <div class="card-header {{ $booking->payment && $booking->payment->payment_status == 'completed' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                <h5 class="mb-0">
                    <i class="fas fa-credit-card"></i> Trạng thái thanh toán
                </h5>
            </div>
            <div class="card-body">
                @if($booking->payment && $booking->payment->payment_status == 'completed')
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h4 class="text-success">Đã thanh toán</h4>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <label class="text-muted small">Phương thức:</label>
                        <p class="fw-bold">
                            @if($booking->payment->payment_method == 'cash')
                                Tiền mặt
                            @elseif($booking->payment->payment_method == 'credit_card')
                                Thẻ tín dụng
                            @elseif($booking->payment->payment_method == 'bank_transfer')
                                Chuyển khoản
                            @elseif($booking->payment->payment_method == 'vnpay')
                                VNPay
                            @elseif($booking->payment->payment_method == 'momo')
                                MoMo
                            @else
                                {{ $booking->payment->payment_method }}
                            @endif
                        </p>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Số tiền:</label>
                        <p class="fw-bold">{{ number_format($booking->payment->amount) }} VNĐ</p>
                    </div>
                    @if($booking->payment->payment_date)
                    <div class="mb-2">
                        <label class="text-muted small">Ngày thanh toán:</label>
                        <p class="fw-bold">{{ $booking->payment->payment_date->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($booking->payment->transaction_id)
                    <div class="mb-2">
                        <label class="text-muted small">Mã giao dịch:</label>
                        <p class="fw-bold"><code>{{ $booking->payment->transaction_id }}</code></p>
                    </div>
                    @endif
                    @if($booking->payment->receipt_image)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">
                            <i class="fas fa-image text-primary"></i> Ảnh biên lai:
                        </label>
                        <div class="mt-2 p-2 bg-light rounded border">
                            <div class="text-center">
                                @php
                                    $imagePath = 'storage/' . $booking->payment->receipt_image;
                                    $imageUrl = asset($imagePath);
                                @endphp
                                <a href="{{ $imageUrl }}" target="_blank" class="d-inline-block">
                                    <img src="{{ $imageUrl }}" 
                                         alt="Biên lai" 
                                         class="img-thumbnail shadow-sm" 
                                         style="max-width: 100%; cursor: pointer; border: 2px solid #dee2e6;"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f8f9fa\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'16\'%3EKhông thể tải ảnh%3C/text%3E%3C/svg%3E';">
                                </a>
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-info-circle"></i> Click vào ảnh để xem kích thước lớn
                                    </small>
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <a href="{{ $imageUrl }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-expand"></i> Mở ảnh
                                        </a>
                                        <a href="{{ $imageUrl }}" 
                                           download 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Tải xuống
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($booking->payment->notes)
                    <hr>
                    <div class="mb-2">
                        <label class="text-muted small">Ghi chú:</label>
                        <p class="mb-0 small">{{ nl2br(e($booking->payment->notes)) }}</p>
                    </div>
                    @endif
                    @if($booking->status == 'completed')
                        {{-- Booking đã hoàn thành --}}
                        <div class="alert alert-success mb-2">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Đã hoàn thành</strong>
                            <p class="mb-0 mt-1 small">Khách hàng đã checkout và booking đã hoàn tất. Phòng đã được trả.</p>
                        </div>
                    @elseif($booking->status != 'checked_in')
                        {{-- Chưa check-in --}}
                        @php
                            $checkInDate = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
                            $today = \Carbon\Carbon::today()->startOfDay();
                            $canCheckIn = $checkInDate->lte($today->copy()->addDay()); // Cho phép check-in sớm 1 ngày
                        @endphp
                        
                        @if($canCheckIn)
                            <form action="{{ route('admin.employee.checkout.checkin', $booking->id) }}" method="POST" class="d-inline w-100 mb-2">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm" 
                                        onclick="return confirm('Xác nhận check-in cho khách hàng này? Phòng sẽ được đánh dấu là occupied.')">
                                    <i class="fas fa-key"></i> Check-in (Nhận phòng)
                                </button>
                            </form>
                            @if($checkInDate->gt($today))
                                <small class="text-info d-block mb-2">
                                    <i class="fas fa-info-circle"></i> Check-in sớm {{ $today->diffInDays($checkInDate) }} ngày
                                </small>
                            @endif
                        @else
                            <button type="button" class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                <i class="fas fa-key"></i> Chưa đến ngày check-in
                            </button>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-info-circle"></i> Chỉ có thể check-in trước 1 ngày hoặc đúng ngày check-in
                            </small>
                        @endif
                    @else
                        {{-- Đã check-in, chưa checkout --}}
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Đã check-in</strong>
                            <p class="mb-0 mt-1 small">Khách hàng đã nhận phòng</p>
                        </div>
                        
                        {{-- Nút checkout --}}
                        @if($booking->status == 'checked_in')
                            @php
                                $checkOutDate = \Carbon\Carbon::parse($booking->check_out_date)->startOfDay();
                                $today = \Carbon\Carbon::today()->startOfDay();
                                $daysEarly = $checkOutDate->diffInDays($today, false);
                            @endphp
                            
                            <button type="button" class="btn btn-success btn-lg w-100 shadow-sm mb-2" 
                                    data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                <i class="fas fa-door-open"></i> Checkout (Trả phòng)
                            </button>
                            
                            @if($daysEarly > 0)
                                <small class="text-warning d-block mb-2">
                                    <i class="fas fa-exclamation-triangle"></i> Trả phòng sớm {{ $daysEarly }} ngày so với dự kiến
                                </small>
                            @elseif($daysEarly < 0)
                                <small class="text-info d-block mb-2">
                                    <i class="fas fa-info-circle"></i> Còn {{ abs($daysEarly) }} ngày đến ngày checkout dự kiến
                                </small>
                            @else
                                <small class="text-success d-block mb-2">
                                    <i class="fas fa-check-circle"></i> Đúng ngày checkout dự kiến
                                </small>
                            @endif
                        @endif
                    @endif
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.employee.invoices.show', $booking->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-invoice"></i> Xem hóa đơn
                        </a>
                        @if($booking->status != 'completed' && $booking->payment->payment_method != 'bank_transfer_qr')
                            <a href="{{ route('admin.employee.payments.create', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Cập nhật thanh toán
                            </a>
                        @endif
                    </div>
                @else
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-2"></i>
                        <h4 class="text-danger">Chưa thanh toán</h4>
                    </div>
                    <hr>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        Khách hàng chưa thanh toán. Vui lòng yêu cầu khách thanh toán trước khi checkout.
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.employee.payments.create', $booking->id) }}" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </a>
                    </div>
                    @if($booking->payment)
                        <div class="mb-2">
                            <label class="text-muted small">Trạng thái:</label>
                            <p class="fw-bold">
                                @if($booking->payment->payment_status == 'pending')
                                    <span class="badge bg-warning">Chờ xác nhận</span>
                                @elseif($booking->payment->payment_status == 'failed')
                                    <span class="badge bg-danger">Thanh toán thất bại</span>
                                @else
                                    <span class="badge bg-secondary">{{ $booking->payment->payment_status }}</span>
                                @endif
                            </p>
                        </div>
                        @if($booking->payment->payment_method == 'bank_transfer_qr' && $booking->payment->payment_status == 'pending')
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Thanh toán QR đang chờ xác nhận</strong>
                                        <p class="mb-0 small mt-1">Khách hàng đã chuyển khoản và đang chờ xác nhận.</p>
                                    </div>
                                    <form action="{{ route('admin.employee.payments.update', $booking->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="payment_status" value="completed">
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Xác nhận thanh toán QR này đã hoàn thành? Booking sẽ được cập nhật thành confirmed.')">
                                            <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                        @if($booking->payment->receipt_image)
                        <hr>
                        <div class="mb-2">
                            <label class="text-muted small fw-bold">
                                <i class="fas fa-image text-primary"></i> Ảnh biên lai:
                            </label>
                            <div class="mt-2 p-2 bg-light rounded border">
                                <div class="text-center">
                                    @php
                                        $imagePath = 'storage/' . $booking->payment->receipt_image;
                                        $imageUrl = asset($imagePath);
                                    @endphp
                                    <a href="{{ $imageUrl }}" target="_blank" class="d-inline-block">
                                        <img src="{{ $imageUrl }}" 
                                             alt="Biên lai" 
                                             class="img-thumbnail shadow-sm" 
                                             style="max-width: 100%; cursor: pointer; border: 2px solid #dee2e6;"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f8f9fa\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'16\'%3EKhông thể tải ảnh%3C/text%3E%3C/svg%3E';">
                                    </a>
                                    <div class="mt-2">
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-info-circle"></i> Click vào ảnh để xem lớn
                                        </small>
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <a href="{{ $imageUrl }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-expand"></i> Mở ảnh
                                            </a>
                                            <a href="{{ $imageUrl }}" 
                                               download 
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> Tải xuống
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($booking->payment->notes)
                        <div class="mb-2">
                            <label class="text-muted small">Ghi chú:</label>
                            <p class="mb-0 small">{{ nl2br(e($booking->payment->notes)) }}</p>
                        </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin nhanh</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Checkout hôm nay:</small>
                    <p class="fw-bold">{{ $booking->check_out_date->format('d/m/Y') }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Giờ checkout:</small>
                    <p class="fw-bold">{{ $booking->check_out_time ?? '12:00' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tính toán checkout sớm (dùng chung cho cả modal và JavaScript) --}}
@php
    $checkOutDate = \Carbon\Carbon::parse($booking->check_out_date)->startOfDay();
    $today = \Carbon\Carbon::today()->startOfDay();
    $isEarlyCheckout = $checkOutDate->gt($today);
    $daysEarly = $isEarlyCheckout ? $today->diffInDays($checkOutDate) : 0;
@endphp

{{-- Modal Checkout với phí phát sinh --}}
@if($booking->status == 'checked_in')
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.employee.checkout.process', $booking->id) }}" method="POST" id="checkoutForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="checkoutModalLabel">
                        <i class="fas fa-door-open"></i> Checkout - Trả phòng
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Thông tin:</strong> 
                        @php
                            $originalPrice = $booking->total_price;
                            $additionalChargesTotal = $booking->additionalCharges ? $booking->additionalCharges->sum('total_price') : 0;
                            $originalPrice = $originalPrice - $additionalChargesTotal;
                        @endphp
                        Tổng tiền phòng: <strong>{{ number_format($originalPrice) }} VNĐ</strong>
                        @if($booking->payment && $booking->payment->payment_status === 'completed')
                            <br><small class="text-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Đã thanh toán tiền phòng.</strong> Chỉ cần thanh toán phí phát sinh (nếu có).
                            </small>
                        @endif
                    </div>

                    <h6 class="mb-3">
                        <i class="fas fa-plus-circle text-primary"></i> Phí phát sinh (nếu có)
                    </h6>
                    
                    @php
                        // Danh sách dịch vụ phổ biến
                        $commonServices = [
                            'Nước uống' => ['default_price' => 15000],
                            'Mì tôm' => ['default_price' => 20000],
                            'Giặt đồ' => ['default_price' => 75000],
                            'Đồ ăn nhẹ' => ['default_price' => 50000],
                            'Dịch vụ phòng' => ['default_price' => 35000],
                            'Đồ uống có cồn' => ['default_price' => 100000],
                            'Đồ ăn sáng' => ['default_price' => 120000],
                            'Dịch vụ spa' => ['default_price' => 350000],
                            'Dịch vụ giặt ủi' => ['default_price' => 50000],
                            'Đồ dùng vệ sinh' => ['default_price' => 25000],
                        ];
                    @endphp
                    
                    <div id="additionalChargesContainer">
                        <div class="charge-item mb-3 p-3 border rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Tên dịch vụ *</label>
                                    <select name="additional_charges[0][service_name]" 
                                            class="form-select form-select-sm service-name-select" 
                                            required>
                                        <option value="">-- Chọn dịch vụ --</option>
                                        @foreach($commonServices as $serviceName => $serviceData)
                                            <option value="{{ $serviceName }}" 
                                                    data-default-price="{{ $serviceData['default_price'] }}">
                                                {{ $serviceName }}
                                            </option>
                                        @endforeach
                                        <option value="__other__">Khác (nhập tùy chỉnh)</option>
                                    </select>
                                    <input type="text" name="additional_charges[0][service_name_custom]" 
                                           class="form-control form-control-sm mt-2 service-name-custom" 
                                           placeholder="Nhập tên dịch vụ..." 
                                           style="display: none;">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Số lượng *</label>
                                    <input type="number" name="additional_charges[0][quantity]" 
                                           class="form-control form-control-sm quantity-input" 
                                           value="1" min="1" step="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Giá đơn vị (VNĐ) *</label>
                                    <input type="number" name="additional_charges[0][unit_price]" 
                                           class="form-control form-control-sm unit-price-input" 
                                           value="0" min="0" step="1000" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Tổng tiền</label>
                                    <input type="text" class="form-control form-control-sm total-price-display" 
                                           value="0" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-danger w-100 remove-charge" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="text" name="additional_charges[0][notes]" 
                                           class="form-control form-control-sm" 
                                           placeholder="Ghi chú (tùy chọn)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" id="addChargeBtn">
                        <i class="fas fa-plus"></i> Thêm dịch vụ phát sinh
                    </button>

                    {{-- Thông báo checkout sớm và hoàn tiền --}}
                    @if($isEarlyCheckout)
                        <hr>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                                <div class="flex-grow-1">
                                    <strong>
                                        <i class="fas fa-calendar-times"></i> 
                                        Checkout sớm {{ $daysEarly }} ngày
                                    </strong>
                                    <p class="mb-1 mt-2">
                                        Khách trả phòng sớm {{ $daysEarly }} ngày so với dự kiến.
                                    </p>
                                    @if($originalPaymentCompleted && $refundAmount > 0)
                                        {{-- Đã thanh toán: hiển thị thông tin hoàn tiền --}}
                                        <div class="bg-light p-3 rounded mt-2">
                                            <p class="mb-1"><strong>Thông tin tính toán:</strong></p>
                                            <ul class="mb-1 small">
                                                <li>Số ngày thực tế đã ở: <strong>{{ $actualNights }} đêm</strong></li>
                                                <li>Tiền phòng thực tế: <strong>{{ number_format($actualRoomPrice) }} VNĐ</strong></li>
                                                <li>Tiền đã thanh toán: <strong>{{ number_format($booking->payment->amount) }} VNĐ</strong></li>
                                            </ul>
                                            <p class="mb-0">
                                                <strong class="text-success">
                                                    <i class="fas fa-money-bill-wave"></i> 
                                                    Số tiền cần hoàn: {{ number_format($refundAmount) }} VNĐ
                                                </strong>
                                            </p>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-info-circle"></i> 
                                                Hệ thống sẽ tự động tạo yêu cầu hoàn tiền sau khi checkout.
                                            </small>
                                        </div>
                                    @elseif($originalPaymentCompleted && $refundAmount <= 0)
                                        {{-- Đã thanh toán nhưng không có tiền hoàn (tiền phòng thực tế >= tiền đã trả) --}}
                                        <div class="bg-light p-3 rounded mt-2">
                                            <p class="mb-0">
                                                <i class="fas fa-check-circle text-success"></i> 
                                                Tiền phòng thực tế ({{ number_format($actualRoomPrice) }} VNĐ) đã được thanh toán đầy đủ. Không cần hoàn tiền.
                                            </p>
                                        </div>
                                    @else
                                        {{-- Chưa thanh toán: tính lại tiền phòng --}}
                                        <div class="bg-light p-3 rounded mt-2">
                                            <p class="mb-1"><strong>Thông tin tính toán:</strong></p>
                                            <ul class="mb-1 small">
                                                <li>Số ngày thực tế đã ở: <strong>{{ $actualNights }} đêm</strong></li>
                                            </ul>
                                            <p class="mb-0">
                                                <strong class="text-primary">
                                                    <i class="fas fa-calculator"></i> 
                                                    Tiền phòng sẽ được tính lại: {{ number_format($actualRoomPrice) }} VNĐ
                                                </strong>
                                            </p>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-calendar-alt"></i> 
                                        Ngày checkout sẽ được cập nhật về ngày hôm nay ({{ \Carbon\Carbon::today()->format('d/m/Y') }})
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr>

                    @php
                        $originalPrice = $booking->total_price;
                        $existingAdditionalCharges = $booking->additionalCharges ? $booking->additionalCharges->sum('total_price') : 0;
                        $originalPrice = $originalPrice - $existingAdditionalCharges;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Tổng tiền phòng:</strong>
                        <strong class="text-primary">{{ number_format($originalPrice) }} VNĐ</strong>
                        @if($booking->payment && $booking->payment->payment_status === 'completed')
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle"></i> Đã thanh toán
                            </span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <strong>Tổng phí phát sinh:</strong>
                        <strong class="text-success" id="totalAdditionalCharges">0 VNĐ</strong>
                    </div>
                    <hr>
                    @if($booking->payment && $booking->payment->payment_status === 'completed')
                        {{-- Nếu đã thanh toán phòng, tổng cộng chỉ là phí phát sinh --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tổng cộng (cần thanh toán):</h5>
                            <h4 class="mb-0 text-danger" id="grandTotal">0 VNĐ</h4>
                        </div>
                        <div class="alert alert-success mt-2 mb-0">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Lưu ý:</strong> Tiền phòng đã thanh toán. Chỉ cần thanh toán <strong id="paymentAmount">0 VNĐ</strong> cho phí phát sinh.
                        </div>
                    @else
                        {{-- Nếu chưa thanh toán phòng, tổng cộng = tiền phòng + phí phát sinh --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tổng cộng:</h5>
                            <h4 class="mb-0 text-danger" id="grandTotal">{{ number_format($originalPrice) }} VNĐ</h4>
                        </div>
                    @endif

                    {{-- Form thanh toán phí phát sinh (luôn hiện để họ có thể thanh toán) --}}
                    <div id="additionalChargesPaymentSection" style="display: block;">
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-credit-card text-info"></i> Thanh toán phí phát sinh
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Phương thức thanh toán *</label>
                                <select name="additional_charges_payment_method" id="additionalChargesPaymentMethod" class="form-select form-select-sm" required>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="bank_transfer_qr">Chuyển khoản QR</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Trạng thái thanh toán *</label>
                                <select name="additional_charges_payment_status" id="additionalChargesPaymentStatus" class="form-select form-select-sm" required>
                                    <option value="completed">Đã thanh toán</option>
                                    <option value="pending">Chờ thanh toán</option>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Nếu chọn "Chờ thanh toán", khách hàng sẽ thanh toán sau. Nếu chọn "Đã thanh toán", hệ thống sẽ ghi nhận thanh toán ngay.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Xác nhận Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let chargeIndex = 1;
    
    // Thêm dịch vụ phát sinh
    document.getElementById('addChargeBtn').addEventListener('click', function() {
        const container = document.getElementById('additionalChargesContainer');
        const newCharge = container.querySelector('.charge-item').cloneNode(true);
        
        // Reset values
        newCharge.querySelectorAll('input').forEach(input => {
            if (input.type === 'text' || input.type === 'number') {
                if (input.name.includes('quantity')) {
                    input.value = 1;
                } else if (input.name.includes('unit_price')) {
                    input.value = 0;
                } else if (input.name.includes('total_price')) {
                    input.value = 0;
                } else {
                    input.value = '';
                }
                // Update name với index mới
                input.name = input.name.replace(/\[\d+\]/, '[' + chargeIndex + ']');
            }
        });
        
        // Reset select
        const select = newCharge.querySelector('.service-name-select');
        if (select) {
            select.selectedIndex = 0;
            select.name = select.name.replace(/\[\d+\]/, '[' + chargeIndex + ']');
        }
        
        // Reset custom input
        const customInput = newCharge.querySelector('.service-name-custom');
        if (customInput) {
            customInput.value = '';
            customInput.style.display = 'none';
            customInput.name = customInput.name.replace(/\[\d+\]/, '[' + chargeIndex + ']');
        }
        
        // Show remove button
        newCharge.querySelector('.remove-charge').style.display = 'block';
        newCharge.querySelector('.total-price-display').value = '0';
        
        container.appendChild(newCharge);
        chargeIndex++;
        
        // Attach event listeners
        attachChargeListeners(newCharge);
        attachServiceSelectListeners(newCharge);
    });
    
    // Remove charge item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-charge')) {
            const chargeItem = e.target.closest('.charge-item');
            if (document.querySelectorAll('.charge-item').length > 1) {
                chargeItem.remove();
                calculateTotal();
            }
        }
    });
    
    // Calculate total for each charge
    function attachChargeListeners(chargeItem) {
        const quantityInput = chargeItem.querySelector('.quantity-input');
        const unitPriceInput = chargeItem.querySelector('.unit-price-input');
        const totalDisplay = chargeItem.querySelector('.total-price-display');
        
        function calculateChargeTotal() {
            const quantity = parseInt(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const total = quantity * unitPrice;
            totalDisplay.value = new Intl.NumberFormat('vi-VN').format(total);
            calculateTotal();
        }
        
        quantityInput.addEventListener('input', calculateChargeTotal);
        unitPriceInput.addEventListener('input', calculateChargeTotal);
    }
    
    // Calculate grand total
    function calculateTotal() {
        let totalAdditional = 0;
        document.querySelectorAll('.charge-item').forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
            const unitPrice = parseFloat(item.querySelector('.unit-price-input').value) || 0;
            totalAdditional += quantity * unitPrice;
        });
        
        // Thêm phí checkout sớm nếu checkbox được chọn
        const earlyCheckoutCheckbox = document.getElementById('calculateEarlyCheckoutPenalty');
        if (earlyCheckoutCheckbox && earlyCheckoutCheckbox.checked) {
            const penaltyAmount = parseFloat(earlyCheckoutCheckbox.dataset.amount || 0);
            totalAdditional += penaltyAmount;
        }
        
        @php
            $originalPrice = $booking->total_price;
            $existingAdditionalCharges = $booking->additionalCharges ? $booking->additionalCharges->sum('total_price') : 0;
            $originalPrice = $originalPrice - $existingAdditionalCharges;
            $isPaid = $booking->payment && $booking->payment->payment_status === 'completed';
        @endphp
        const roomPrice = {{ $originalPrice }};
        const isPaid = {{ $isPaid ? 'true' : 'false' }};
        
        // Nếu đã thanh toán phòng, tổng cộng chỉ là phí phát sinh
        // Nếu chưa thanh toán, tổng cộng = tiền phòng + phí phát sinh
        const grandTotal = isPaid ? totalAdditional : (roomPrice + totalAdditional);
        
        document.getElementById('totalAdditionalCharges').textContent = 
            new Intl.NumberFormat('vi-VN').format(totalAdditional) + ' VNĐ';
        document.getElementById('grandTotal').textContent = 
            new Intl.NumberFormat('vi-VN').format(grandTotal) + ' VNĐ';
        
        // Cập nhật số tiền cần thanh toán (chỉ phí phát sinh nếu đã thanh toán phòng)
        @if($booking->payment && $booking->payment->payment_status === 'completed')
        const paymentAmountElement = document.getElementById('paymentAmount');
        if (paymentAmountElement) {
            paymentAmountElement.textContent = new Intl.NumberFormat('vi-VN').format(totalAdditional) + ' VNĐ';
        }
        @endif
        
        // Hiện/ẩn form thanh toán phí phát sinh
        const paymentSection = document.getElementById('additionalChargesPaymentSection');
        if (totalAdditional > 0) {
            paymentSection.style.display = 'block';
            // Set required cho các field thanh toán
            document.getElementById('additionalChargesPaymentMethod').required = true;
            
            // Nếu booking đã thanh toán, tự động set completed và disable select
            @if($booking->payment && $booking->payment->payment_status === 'completed')
            const paymentStatusSelect = document.getElementById('additionalChargesPaymentStatus');
            if (paymentStatusSelect) {
                paymentStatusSelect.value = 'completed';
                paymentStatusSelect.disabled = true;
                // Thêm note
                const noteDiv = document.createElement('small');
                noteDiv.className = 'text-info d-block mt-1';
                noteDiv.innerHTML = '<i class="fas fa-info-circle"></i> Booking đã thanh toán, phí phát sinh tự động đánh dấu "Đã thanh toán"';
                if (!paymentStatusSelect.nextElementSibling || !paymentStatusSelect.nextElementSibling.classList.contains('text-info')) {
                    paymentStatusSelect.parentElement.appendChild(noteDiv);
                }
            }
            @else
            document.getElementById('additionalChargesPaymentStatus').required = true;
            @endif
        } else {
            paymentSection.style.display = 'none';
            document.getElementById('additionalChargesPaymentMethod').required = false;
            document.getElementById('additionalChargesPaymentStatus').required = false;
        }
    }
    
    // Xử lý checkbox checkout sớm
    const earlyCheckoutCheckbox = document.getElementById('calculateEarlyCheckoutPenalty');
    if (earlyCheckoutCheckbox) {
        @if(isset($isEarlyCheckout) && $isEarlyCheckout && isset($daysEarly) && $daysEarly > 0 && $booking->room)
        earlyCheckoutCheckbox.dataset.amount = {{ $daysEarly * $booking->room->price_per_night * 0.5 }};
        @endif
        earlyCheckoutCheckbox.addEventListener('change', function() {
            calculateTotal();
        });
    }
    
    // Xử lý khi chọn dịch vụ từ select
    function attachServiceSelectListeners(chargeItem) {
        const select = chargeItem.querySelector('.service-name-select');
        const customInput = chargeItem.querySelector('.service-name-custom');
        const unitPriceInput = chargeItem.querySelector('.unit-price-input');
        
        if (select) {
            select.addEventListener('change', function() {
                const selectedValue = this.value;
                
                if (selectedValue === '__other__') {
                    // Hiện input tùy chỉnh
                    if (customInput) {
                        customInput.style.display = 'block';
                        customInput.required = true;
                        customInput.name = select.name; // Đổi name để match với select
                        select.disabled = true; // Disable select
                        select.required = false;
                        customInput.focus();
                    }
                } else if (selectedValue) {
                    // Ẩn input tùy chỉnh
                    if (customInput) {
                        customInput.style.display = 'none';
                        customInput.value = '';
                        customInput.required = false;
                        customInput.name = customInput.name.replace(/\[service_name\]/, '[service_name_custom]'); // Đổi lại name
                        select.disabled = false; // Enable select
                        select.required = true;
                    }
                    
                    // Tự động điền giá mặc định
                    const defaultPrice = this.options[this.selectedIndex].dataset.defaultPrice;
                    if (defaultPrice && unitPriceInput) {
                        unitPriceInput.value = defaultPrice;
                        // Trigger tính toán lại
                        unitPriceInput.dispatchEvent(new Event('input'));
                    }
                } else {
                    // Reset (chọn "-- Chọn dịch vụ --")
                    if (customInput) {
                        customInput.style.display = 'none';
                        customInput.value = '';
                        customInput.required = false;
                        customInput.name = customInput.name.replace(/\[service_name\]/, '[service_name_custom]'); // Đổi lại name
                    }
                    if (select) {
                        select.disabled = false;
                        select.required = true;
                    }
                    if (unitPriceInput) {
                        unitPriceInput.value = 0;
                        unitPriceInput.dispatchEvent(new Event('input'));
                    }
                }
            });
        }
    }
    
    // Logic submit đã được xử lý bằng cách đổi name của custom input
    // Khi chọn "Khác", custom input sẽ có name giống select, và select bị disable
    // Khi chọn từ select, custom input bị disable và select được enable
    
    // Attach listeners to initial charge item
    document.querySelectorAll('.charge-item').forEach(item => {
        attachChargeListeners(item);
        attachServiceSelectListeners(item);
    });
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        let hasValidCharge = false;
        let hasAnyInput = false;
        let totalAdditional = 0;
        
        document.querySelectorAll('.charge-item').forEach(item => {
            const select = item.querySelector('.service-name-select');
            const customInput = item.querySelector('.service-name-custom');
            const quantity = item.querySelector('.quantity-input').value;
            const unitPrice = item.querySelector('.unit-price-input').value;
            
            // Lấy tên dịch vụ từ select hoặc custom input
            let serviceName = '';
            if (select && !select.disabled && select.value && select.value !== '__other__') {
                // Chọn từ select
                serviceName = select.value;
            } else if (select && select.disabled && customInput && customInput.value.trim()) {
                // Chọn "Khác" và đã nhập
                serviceName = customInput.value.trim();
            }
            
            if (serviceName || quantity !== '1' || unitPrice !== '0') {
                hasAnyInput = true;
            }
            
            if (serviceName && quantity && unitPrice) {
                if (parseInt(quantity) > 0 && parseFloat(unitPrice) > 0) {
                    hasValidCharge = true;
                    totalAdditional += parseInt(quantity) * parseFloat(unitPrice);
                }
            }
        });
        
        if (hasAnyInput && !hasValidCharge) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ thông tin cho các dịch vụ phát sinh (tên, số lượng, giá) hoặc xóa các dịch vụ không sử dụng.');
            return false;
        }
        
        // Kiểm tra form thanh toán phí phát sinh nếu có phí phát sinh
        if (totalAdditional > 0) {
            const paymentMethod = document.getElementById('additionalChargesPaymentMethod').value;
            const paymentStatus = document.getElementById('additionalChargesPaymentStatus').value;
            
            if (!paymentMethod || !paymentStatus) {
                e.preventDefault();
                alert('Vui lòng chọn phương thức và trạng thái thanh toán cho phí phát sinh.');
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection

