@extends('layouts.admin')

@section('title', 'Chi tiết Yêu cầu Hoàn tiền #' . $refundRequest->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-money-bill-wave"></i> Chi tiết Yêu cầu Hoàn tiền #{{ $refundRequest->id }}</h2>
    <div>
        <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Refund Request Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin yêu cầu hoàn tiền</h5>
                    @if($refundRequest->status === 'pending')
                        <span class="badge bg-warning fs-6">Chờ xử lý</span>
                    @elseif($refundRequest->status === 'approved')
                        <span class="badge bg-info fs-6">Đã duyệt</span>
                    @elseif($refundRequest->status === 'rejected')
                        <span class="badge bg-danger fs-6">Đã từ chối</span>
                    @else
                        <span class="badge bg-success fs-6">Đã hoàn thành</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã yêu cầu:</label>
                        <p class="fw-bold">#{{ $refundRequest->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày tạo:</label>
                        <p class="fw-bold">{{ $refundRequest->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số tiền hoàn:</label>
                        <p class="fw-bold text-primary fs-4">{{ number_format($refundRequest->refund_amount) }} VNĐ</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phương thức hoàn tiền:</label>
                        <p class="fw-bold mb-0">
                            @if($refundRequest->refund_method === 'bank_transfer')
                                <i class="fas fa-university text-info"></i> Chuyển khoản ngân hàng
                            @else
                                <i class="fas fa-qrcode text-secondary"></i> QR Code
                            @endif
                        </p>
                    </div>
                </div>

                @if($refundRequest->refund_method === 'bank_transfer')
                    <hr>
                    <h6 class="text-muted mb-3">Thông tin tài khoản nhận tiền</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tên ngân hàng:</label>
                            <p class="fw-bold">{{ $refundRequest->bank_name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Số tài khoản:</label>
                            <p class="fw-bold"><code>{{ $refundRequest->account_number ?? '-' }}</code></p>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted small">Tên chủ tài khoản:</label>
                            <p class="fw-bold">{{ $refundRequest->account_holder_name ?? '-' }}</p>
                        </div>
                    </div>
                @else
                    <hr>
                    <h6 class="text-muted mb-3">Thông tin QR Code</h6>
                    <div class="mb-3">
                        @if($refundRequest->qr_code && str_starts_with($refundRequest->qr_code, 'refunds/qr_codes/'))
                            {{-- Nếu là ảnh --}}
                            <label class="text-muted small fw-bold">
                                <i class="fas fa-image text-primary"></i> Ảnh QR Code:
                            </label>
                            <div class="mt-2 p-3 bg-light rounded border">
                                <div class="text-center">
                                    @php
                                        $imagePath = 'storage/' . $refundRequest->qr_code;
                                        $imageUrl = asset($imagePath);
                                    @endphp
                                    <a href="{{ $imageUrl }}" target="_blank" class="d-inline-block">
                                        <img src="{{ $imageUrl }}" 
                                             alt="QR Code" 
                                             class="img-thumbnail shadow-sm" 
                                             style="max-width: 400px; width: 100%; cursor: pointer; border: 2px solid #dee2e6;"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f8f9fa\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'16\'%3EKhông thể tải ảnh%3C/text%3E%3C/svg%3E';">
                                    </a>
                                    <div class="mt-3">
                                        <a href="{{ $imageUrl }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-expand"></i> Mở ảnh trong tab mới
                                        </a>
                                        <a href="{{ $imageUrl }}" download class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Tải ảnh xuống
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Nếu là text --}}
                            <label class="text-muted small">Thông tin QR Code:</label>
                            <div class="p-3 bg-light rounded border">
                                <p class="mb-0">{{ nl2br(e($refundRequest->qr_code)) }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($refundRequest->notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Ghi chú từ khách hàng:</label>
                        <div class="p-3 bg-light rounded border">
                            <p class="mb-0">{{ nl2br(e($refundRequest->notes)) }}</p>
                        </div>
                    </div>
                @endif

                @if($refundRequest->admin_notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Ghi chú từ admin:</label>
                        <div class="p-3 bg-light rounded border">
                            <p class="mb-0">{{ nl2br(e($refundRequest->admin_notes)) }}</p>
                        </div>
                    </div>
                @endif

                @if($refundRequest->processed_at)
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Người xử lý:</label>
                            <p class="fw-bold">{{ $refundRequest->processedBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Thời gian xử lý:</label>
                            <p class="fw-bold">{{ $refundRequest->processed_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Booking Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đặt phòng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã đặt phòng:</label>
                        <p class="fw-bold">
                            <a href="{{ route('admin.bookings.show', $refundRequest->booking_id) }}">
                                #{{ $refundRequest->booking_id }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Trạng thái:</label>
                        <p class="mb-0">
                            @if($refundRequest->booking->status === 'cancelled')
                                <span class="badge bg-danger">Đã hủy</span>
                            @else
                                <span class="badge bg-secondary">{{ $refundRequest->booking->status }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin phòng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số phòng:</label>
                        <p class="fw-bold">{{ $refundRequest->booking->room->room_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Loại phòng:</label>
                        <p class="fw-bold">{{ $refundRequest->booking->room->room_type }}</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin khách hàng</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Họ tên:</label>
                        <p class="fw-bold">{{ $refundRequest->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email:</label>
                        <p class="fw-bold">{{ $refundRequest->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số điện thoại:</label>
                        <p class="fw-bold">{{ $refundRequest->user->phone ?? '-' }}</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted mb-3">Thông tin thanh toán</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã thanh toán:</label>
                        <p class="fw-bold">#{{ $refundRequest->payment->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Số tiền đã thanh toán:</label>
                        <p class="fw-bold text-primary">{{ number_format($refundRequest->payment->amount) }} VNĐ</p>
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
                @if($refundRequest->status === 'pending')
                    <form action="{{ route('admin.refunds.approve', $refundRequest->id) }}" method="POST" class="mb-2">
                        @csrf
                        <div class="mb-3">
                            <label for="approve_notes" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea name="admin_notes" id="approve_notes" class="form-control" rows="3" 
                                      placeholder="Nhập ghi chú nếu có..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('Xác nhận duyệt yêu cầu hoàn tiền này?')">
                            <i class="fas fa-check-circle"></i> Duyệt yêu cầu
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger w-100 mb-2" 
                            data-bs-toggle="modal" data-bs-target="#rejectRefundModal">
                        <i class="fas fa-times-circle"></i> Từ chối yêu cầu
                    </button>
                @elseif($refundRequest->status === 'approved')
                    <form action="{{ route('admin.refunds.complete', $refundRequest->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="complete_notes" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea name="admin_notes" id="complete_notes" class="form-control" rows="3" 
                                      placeholder="Nhập ghi chú về việc hoàn tiền..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('Xác nhận đã hoàn tiền thành công?')">
                            <i class="fas fa-check-double"></i> Đánh dấu đã hoàn tiền
                        </button>
                    </form>
                @endif

                <hr>

                <a href="{{ route('admin.bookings.show', $refundRequest->booking_id) }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-calendar-check"></i> Xem đặt phòng
                </a>

                <a href="{{ route('admin.payments.show', $refundRequest->payment_id) }}" class="btn btn-secondary w-100">
                    <i class="fas fa-dollar-sign"></i> Xem thanh toán
                </a>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tóm tắt</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Số tiền đã thanh toán:</span>
                    <strong>{{ number_format($refundRequest->payment->amount) }} ₫</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Số tiền hoàn:</span>
                    <strong class="text-primary">{{ number_format($refundRequest->refund_amount) }} ₫</strong>
                </div>
                @if($refundRequest->payment->amount > $refundRequest->refund_amount)
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Phí hủy phòng:</span>
                        <strong class="text-danger">
                            {{ number_format($refundRequest->payment->amount - $refundRequest->refund_amount) }} ₫
                        </strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối yêu cầu -->
@if($refundRequest->status === 'pending')
<div class="modal fade" id="rejectRefundModal" tabindex="-1" aria-labelledby="rejectRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.refunds.reject', $refundRequest->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectRefundModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger"></i> Từ chối yêu cầu hoàn tiền
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Lưu ý:</strong> Bạn đang từ chối yêu cầu hoàn tiền này. Vui lòng nhập lý do từ chối.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_notes" class="form-label">
                            Lý do từ chối <span class="text-danger">*</span>
                        </label>
                        <textarea name="admin_notes" id="reject_notes" class="form-control @error('admin_notes') is-invalid @enderror" rows="4" 
                                  placeholder="Nhập lý do từ chối yêu cầu hoàn tiền (bắt buộc)" required>{{ old('admin_notes') }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

