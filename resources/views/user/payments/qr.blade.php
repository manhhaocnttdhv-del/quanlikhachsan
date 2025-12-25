@extends('layouts.app')

@section('title', 'QR Code Chuyển khoản')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-qrcode"></i> Thanh toán qua QR Code</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <h5 class="text-primary">Quét mã QR để chuyển khoản</h5>
                        <p class="text-muted">Sử dụng ứng dụng ngân hàng để quét mã QR bên dưới</p>
                    </div>

                    <!-- QR Code -->
                    <div class="mb-4 p-4 bg-light rounded">
                        <div class="d-flex justify-content-center">
                            @php
                                // Đảm bảo QR data chỉ chứa ASCII để tránh lỗi encoding
                                $qrDataSafe = mb_convert_encoding($qrData, 'ASCII', 'UTF-8');
                                // Nếu có ký tự không thể convert, thay thế bằng ký tự tương đương
                                $qrDataSafe = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $qrData);
                            @endphp
                            {!! QrCode::size(300)->errorCorrection('H')->encoding('UTF-8')->generate($qrDataSafe) !!}
                        </div>
                        <p class="text-muted mt-2 small">Quét mã QR bằng ứng dụng ngân hàng</p>
                    </div>

                    <!-- Thông tin chuyển khoản -->
                    <div class="card mb-4">
                        <div class="card-body text-start">
                            <h6 class="card-title mb-3"><i class="fas fa-info-circle text-primary"></i> Thông tin chuyển khoản</h6>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Số tài khoản:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-primary fs-6">{{ $bankAccount }}</span>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $bankAccount }}')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Ngân hàng:</strong></div>
                                <div class="col-sm-8">{{ $bankName }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Chủ tài khoản:</strong></div>
                                <div class="col-sm-8">{{ $accountName }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Số tiền:</strong></div>
                                <div class="col-sm-8">
                                    <span class="text-danger fw-bold fs-5">{{ number_format($payment->amount) }} VNĐ</span>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ number_format($payment->amount) }}')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Nội dung:</strong></div>
                                <div class="col-sm-8">
                                    <code class="bg-light p-2 rounded">{{ $transferContent }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $transferContent }}')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4"><strong>Mã đặt phòng:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-info">#{{ $payment->booking_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin đặt phòng -->
                    <div class="card mb-4">
                        <div class="card-body text-start">
                            <h6 class="card-title mb-3"><i class="fas fa-calendar-check text-success"></i> Thông tin đặt phòng</h6>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Phòng:</strong></div>
                                <div class="col-sm-8">{{ $payment->booking->room->room_number }} - {{ $payment->booking->room->room_type }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Ngày nhận:</strong></div>
                                <div class="col-sm-8">{{ $payment->booking->check_in_date->format('d/m/Y') }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Ngày trả:</strong></div>
                                <div class="col-sm-8">{{ $payment->booking->check_out_date->format('d/m/Y') }}</div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4"><strong>Tổng tiền:</strong></div>
                                <div class="col-sm-8">
                                    <span class="text-primary fw-bold">{{ number_format($payment->amount) }} VNĐ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hướng dẫn -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Hướng dẫn thanh toán:</h6>
                        <ol class="text-start mb-0">
                            <li>Mở ứng dụng ngân hàng trên điện thoại</li>
                            <li>Chọn chức năng "Quét QR" hoặc "Chuyển khoản"</li>
                            <li>Quét mã QR code ở trên hoặc nhập thông tin chuyển khoản</li>
                            <li>Kiểm tra thông tin và xác nhận chuyển khoản</li>
                            <li>Sau khi chuyển khoản thành công, vui lòng chụp ảnh biên lai và gửi cho chúng tôi</li>
                        </ol>
                    </div>

                    <!-- Lưu ý -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Lưu ý:</strong> Vui lòng chuyển đúng số tiền và nội dung chuyển khoản để đảm bảo thanh toán được xử lý nhanh chóng.
                    </div>

                    @if($payment->payment_status === 'pending')
                    <!-- Form xác nhận đã chuyển khoản -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Xác nhận đã chuyển khoản</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.payments.confirm', $payment->id) }}" method="POST" enctype="multipart/form-data" id="confirmForm">
                                @csrf
                                
                                <!-- Nút hủy thanh toán -->
                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelPaymentModal">
                                        <i class="fas fa-times"></i> Hủy thanh toán QR
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mã giao dịch từ ngân hàng (nếu có)</label>
                                    <input type="text" name="transaction_id" class="form-control" 
                                           placeholder="Nhập mã giao dịch từ ngân hàng">
                                    <small class="text-muted">Mã giao dịch từ SMS hoặc email xác nhận của ngân hàng</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Upload ảnh biên lai (tùy chọn)</label>
                                    <input type="file" name="receipt_image" class="form-control" 
                                           accept="image/*" id="receiptImage" onchange="previewImage(this)">
                                    <small class="text-muted">Chụp ảnh biên lai chuyển khoản từ ứng dụng ngân hàng (JPG, PNG, tối đa 2MB)</small>
                                    <div id="imagePreview" class="mt-2" style="display: none;">
                                        <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px;">
                                        <p class="text-muted small mt-1">Preview ảnh biên lai</p>
                                    </div>
                                    @if($payment->receipt_image)
                                        <div class="mt-2">
                                            <p class="text-muted small mb-1">Ảnh biên lai hiện tại:</p>
                                            <img src="{{ asset('storage/' . $payment->receipt_image) }}" 
                                                 alt="Biên lai" class="img-thumbnail" style="max-width: 300px;">
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ghi chú (tùy chọn)</label>
                                    <textarea name="notes" class="form-control" rows="3" 
                                              placeholder="Ghi chú thêm về giao dịch chuyển khoản"></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Lưu ý:</strong> Sau khi xác nhận, thanh toán sẽ ở trạng thái "Chờ xác nhận". 
                                    Admin sẽ kiểm tra và xác nhận thanh toán trong thời gian sớm nhất.
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check-circle"></i> Xác nhận đã chuyển khoản
                                </button>
                            </form>
                        </div>
                    </div>
                    @elseif($payment->payment_status === 'completed')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Thanh toán đã được xác nhận!</strong> Đặt phòng của bạn đã được xác nhận thành công.
                    </div>
                    @elseif($payment->payment_status === 'failed')
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
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('user.payments.create', $payment->booking_id) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card"></i> Thanh toán lại
                            </a>
                        </div>
                    @endif
                    @endif

                    <!-- Nút hành động -->
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="{{ route('user.payments.show', $payment->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Xem chi tiết thanh toán
                        </a>
                        @if($payment->payment_status === 'pending' && $payment->payment_method === 'bank_transfer_qr')
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPaymentModal">
                                <i class="fas fa-times"></i> Hủy thanh toán
                            </button>
                        @endif
                        <a href="{{ route('user.bookings.show', $payment->booking_id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button class="btn btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Làm mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Cập nhật CSRF token từ meta tag
    document.addEventListener('DOMContentLoaded', function() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            // Cập nhật CSRF token trong form
            const form = document.getElementById('confirmForm');
            if (form) {
                const csrfInput = form.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfInput.value = token.getAttribute('content');
                }
            }
        }
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Đã sao chép: ' + text);
        }, function(err) {
            // Fallback cho trình duyệt cũ
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Đã sao chép: ' + text);
        });
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Xử lý lỗi 419 khi submit form
    document.getElementById('confirmForm')?.addEventListener('submit', function(e) {
        // Đảm bảo CSRF token được cập nhật trước khi submit
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            const csrfInput = this.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = token.getAttribute('content');
            }
        }
    });
</script>

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
@endpush

