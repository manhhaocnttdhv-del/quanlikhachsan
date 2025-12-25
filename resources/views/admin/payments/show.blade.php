@extends('layouts.admin')

@section('title', 'Chi tiết thanh toán #' . $payment->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-dollar-sign"></i> Chi tiết thanh toán #{{ $payment->id }}</h2>
    <div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Payment Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin thanh toán</h5>
                    @if($payment->payment_status == 'completed')
                        <span class="badge bg-success fs-6">Hoàn thành</span>
                    @elseif($payment->payment_status == 'pending')
                        <span class="badge bg-warning fs-6">Chờ xử lý</span>
                    @elseif($payment->payment_status == 'failed')
                        <span class="badge bg-danger fs-6">Thất bại</span>
                    @else
                        <span class="badge bg-secondary fs-6">Đã hoàn tiền</span>
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
                            <p class="fw-bold"><code>{{ $payment->transaction_id }}</code></p>
                        </div>
                    </div>
                @endif

                @if($payment->receipt_image)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">
                            <i class="fas fa-image text-primary"></i> Ảnh biên lai:
                        </label>
                        <div class="mt-2 p-3 bg-light rounded border">
                            <div class="text-center">
                                @php
                                    $imagePath = 'storage/' . $payment->receipt_image;
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
                                        Đường dẫn: <code>{{ $payment->receipt_image }}</code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($payment->payment_method == 'bank_transfer_qr' && $payment->payment_status == 'pending')
                    <hr>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Chưa có ảnh biên lai</strong>
                        <p class="mb-0 mt-2">Khách hàng chưa upload ảnh biên lai thanh toán. Bạn có thể yêu cầu khách hàng gửi ảnh biên lai để xác nhận thanh toán.</p>
                    </div>
                @endif

                @if($payment->notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Ghi chú:</label>
                        <p class="mb-0">{{ nl2br(e($payment->notes)) }}</p>
                    </div>
                @endif

                @if($payment->payment_status === 'failed' && $payment->payment_method === 'bank_transfer_qr')
                    <hr>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Thanh toán đã bị từ chối/hủy!</strong>
                        @php
                            $notes = $payment->notes ?? '';
                            $rejectReason = '';
                            if (str_contains($notes, '[ADMIN]')) {
                                // Tìm lý do từ chối từ admin
                                $parts = explode('[ADMIN]', $notes);
                                if (count($parts) > 1) {
                                    $adminNote = $parts[1];
                                    if (preg_match('/Lý do:\s*(.+?)(?:\n|$)/', $adminNote, $matches)) {
                                        $rejectReason = trim($matches[1]);
                                    }
                                }
                            } elseif (str_contains($notes, 'Đã hủy thanh toán QR')) {
                                // Tìm lý do hủy từ user
                                if (preg_match('/Lý do:\s*(.+?)(?:\n|$)/', $notes, $matches)) {
                                    $rejectReason = trim($matches[1]);
                                }
                            }
                        @endphp
                        @if($rejectReason)
                            <p class="mb-0 mt-2">
                                <strong>Lý do:</strong> {{ $rejectReason }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Booking Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đặt phòng liên quan</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã đặt phòng:</label>
                        <p class="fw-bold">
                            <a href="{{ route('admin.bookings.show', $payment->booking->id) }}">
                                #{{ $payment->booking->id }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Trạng thái đặt phòng:</label>
                        <p class="mb-0">
                            @if($payment->booking->status == 'pending')
                                <span class="badge bg-warning">Chờ xử lý</span>
                            @elseif($payment->booking->status == 'confirmed')
                                <span class="badge bg-success">Đã xác nhận</span>
                            @elseif($payment->booking->status == 'checked_in')
                                <span class="badge bg-info">Đã nhận phòng</span>
                            @elseif($payment->booking->status == 'checked_out')
                                <span class="badge bg-secondary">Đã trả phòng</span>
                            @else
                                <span class="badge bg-danger">Đã hủy</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->room->room_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Loại phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->room->room_type }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Giá/đêm:</label>
                        <p class="fw-bold">{{ number_format($payment->booking->room->price_per_night) }} VNĐ</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sức chứa:</label>
                        <p class="fw-bold">{{ $payment->booking->room->capacity }} người</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin khách hàng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Họ tên:</label>
                        <p class="fw-bold">{{ $payment->booking->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email:</label>
                        <p class="fw-bold">{{ $payment->booking->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số điện thoại:</label>
                        <p class="fw-bold">{{ $payment->booking->user->phone ?? '-' }}</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Chi tiết đặt phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày nhận phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->check_in_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày trả phòng:</label>
                        <p class="fw-bold">{{ $payment->booking->check_out_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số đêm:</label>
                        <p class="fw-bold">{{ $payment->booking->check_in_date->diffInDays($payment->booking->check_out_date) }} đêm</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số người:</label>
                        <p class="fw-bold">{{ $payment->booking->number_of_guests }} người</p>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted small">Tổng tiền đặt phòng:</label>
                        <p class="fw-bold text-primary fs-5">{{ number_format($payment->booking->total_price) }} VNĐ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Actions Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hành động</h5>
            </div>
            <div class="card-body">
                @if($payment->payment_status === 'pending' && $payment->payment_method === 'bank_transfer_qr')
                    <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="payment_status" value="completed">
                        <input type="hidden" name="notes" value="{{ $payment->notes }}">
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('Xác nhận thanh toán này đã hoàn thành?')">
                            <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger w-100 mb-2" 
                            data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">
                        <i class="fas fa-times-circle"></i> Từ chối thanh toán
                    </button>
                @endif

                
                <a href="{{ route('admin.bookings.show', $payment->booking->id) }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-calendar-check"></i> Xem đặt phòng
                </a>

                <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" 
                            onclick="return confirm('Bạn có chắc muốn xóa thanh toán này?')">
                        <i class="fas fa-trash"></i> Xóa thanh toán
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment Summary Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tóm tắt</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Số tiền:</span>
                    <strong class="text-primary">{{ number_format($payment->amount) }} ₫</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Tổng đặt phòng:</span>
                    <strong>{{ number_format($payment->booking->total_price) }} ₫</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Còn lại:</span>
                    <strong class="text-{{ ($payment->booking->total_price - $payment->amount) > 0 ? 'danger' : 'success' }}">
                        {{ number_format($payment->booking->total_price - $payment->amount) }} ₫
                    </strong>
                </div>
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
                        <strong>Tạo thanh toán</strong>
                        <br>
                        <small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small>
                    </li>
                    
                    @if($payment->payment_status == 'completed' && $payment->payment_date)
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Hoàn thành thanh toán</strong>
                            <br>
                            <small class="text-muted">{{ $payment->payment_date->format('d/m/Y H:i') }}</small>
                        </li>
                    @endif

                    @if($payment->payment_status == 'failed')
                        <li class="mb-3">
                            <i class="fas fa-times-circle text-danger"></i>
                            <strong>Thanh toán thất bại</strong>
                            <br>
                            <small class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i') }}</small>
                        </li>
                    @endif

                    @if($payment->payment_status == 'refunded')
                        <li class="mb-3">
                            <i class="fas fa-undo text-secondary"></i>
                            <strong>Đã hoàn tiền</strong>
                            <br>
                            <small class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i') }}</small>
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

<!-- Modal từ chối thanh toán -->
@if($payment->payment_status === 'pending' && $payment->payment_method === 'bank_transfer_qr')
<div class="modal fade" id="rejectPaymentModal" tabindex="-1" aria-labelledby="rejectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectPaymentModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger"></i> Từ chối thanh toán QR
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Lưu ý:</strong> Bạn đang từ chối thanh toán QR này. Khách hàng sẽ được thông báo và có thể chọn phương thức thanh toán khác.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">
                            Lý do từ chối <span class="text-danger">*</span>
                        </label>
                        <textarea name="reject_reason" id="reject_reason" class="form-control @error('reject_reason') is-invalid @enderror" rows="4" 
                                  placeholder="Nhập lý do từ chối thanh toán (bắt buộc)" required>{{ old('reject_reason') }}</textarea>
                        @error('reject_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ví dụ: Không tìm thấy giao dịch trong hệ thống ngân hàng, Số tiền không khớp, Ảnh biên lai không rõ ràng, v.v.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check"></i> Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

