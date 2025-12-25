@extends('layouts.admin')

@section('title', 'Tạo báo cáo ca của tôi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-plus"></i> Tạo báo cáo ca của tôi</h2>
    <a href="{{ route('admin.employee.reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

@if($shift)
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Thông tin ca làm việc</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="text-muted small">Ngày:</label>
                <p class="fw-bold">{{ $shift->shift_date->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Giờ:</label>
                <p class="fw-bold">{{ $shift->start_time }} - {{ $shift->end_time }}</p>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Trạng thái:</label>
                <p>
                    @if($shift->status == 'active')
                        <span class="badge bg-success">Đang làm</span>
                    @elseif($shift->status == 'completed')
                        <span class="badge bg-secondary">Hoàn thành</span>
                    @else
                        <span class="badge bg-info">Đã lên lịch</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.employee.reports.store') }}" method="POST">
            @csrf

            <input type="hidden" name="shift_id" value="{{ $shift->id ?? '' }}">
            <input type="hidden" name="report_date" value="{{ $shift->shift_date->format('Y-m-d') ?? date('Y-m-d') }}">

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Lưu ý:</strong> Các giá trị dưới đây đã được tính tự động từ hệ thống. Bạn có thể chỉnh sửa nếu cần.
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Doanh thu theo phương thức</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tiền mặt (VNĐ) *</label>
                                <input type="number" name="cash_amount" step="0.01" min="0" 
                                       class="form-control @error('cash_amount') is-invalid @enderror" 
                                       value="{{ old('cash_amount', $revenue['cash_amount'] ?? 0) }}" required>
                                @error('cash_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Thẻ tín dụng (VNĐ) *</label>
                                <input type="number" name="card_amount" step="0.01" min="0" 
                                       class="form-control @error('card_amount') is-invalid @enderror" 
                                       value="{{ old('card_amount', $revenue['card_amount'] ?? 0) }}" required>
                                @error('card_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chuyển khoản (VNĐ) *</label>
                                <input type="number" name="transfer_amount" step="0.01" min="0" 
                                       class="form-control @error('transfer_amount') is-invalid @enderror" 
                                       value="{{ old('transfer_amount', $revenue['transfer_amount'] ?? 0) }}" required>
                                @error('transfer_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Khác (VNĐ) *</label>
                                <input type="number" name="other_amount" step="0.01" min="0" 
                                       class="form-control @error('other_amount') is-invalid @enderror" 
                                       value="{{ old('other_amount', $revenue['other_amount'] ?? 0) }}" required>
                                @error('other_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tổng doanh thu (VNĐ) *</label>
                                <input type="number" name="total_revenue" step="0.01" min="0" 
                                       class="form-control @error('total_revenue') is-invalid @enderror" 
                                       value="{{ old('total_revenue', $revenue['total_revenue'] ?? 0) }}" 
                                       id="total_revenue" required readonly>
                                @error('total_revenue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tự động tính từ tổng các phương thức</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Thống kê checkout</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tổng số checkout *</label>
                                <input type="number" name="total_checkouts" min="0" 
                                       class="form-control @error('total_checkouts') is-invalid @enderror" 
                                       value="{{ old('total_checkouts', $revenue['total_checkouts'] ?? 0) }}" required>
                                @error('total_checkouts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Đã thanh toán *</label>
                                <input type="number" name="paid_checkouts" min="0" 
                                       class="form-control @error('paid_checkouts') is-invalid @enderror" 
                                       value="{{ old('paid_checkouts', $revenue['paid_checkouts'] ?? 0) }}" required>
                                @error('paid_checkouts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chưa thanh toán *</label>
                                <input type="number" name="unpaid_checkouts" min="0" 
                                       class="form-control @error('unpaid_checkouts') is-invalid @enderror" 
                                       value="{{ old('unpaid_checkouts', $revenue['unpaid_checkouts'] ?? 0) }}" required>
                                @error('unpaid_checkouts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" 
                          placeholder="Ghi chú về ca làm việc...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái *</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Lưu nháp</option>
                    <option value="submitted" {{ old('status') == 'submitted' ? 'selected' : '' }}>Gửi báo cáo</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Chọn "Lưu nháp" để chỉnh sửa sau, hoặc "Gửi báo cáo" để hoàn tất.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu báo cáo
                </button>
                <a href="{{ route('admin.employee.reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cashInput = document.querySelector('input[name="cash_amount"]');
    const cardInput = document.querySelector('input[name="card_amount"]');
    const transferInput = document.querySelector('input[name="transfer_amount"]');
    const otherInput = document.querySelector('input[name="other_amount"]');
    const totalInput = document.getElementById('total_revenue');

    function calculateTotal() {
        const cash = parseFloat(cashInput.value) || 0;
        const card = parseFloat(cardInput.value) || 0;
        const transfer = parseFloat(transferInput.value) || 0;
        const other = parseFloat(otherInput.value) || 0;
        const total = cash + card + transfer + other;
        totalInput.value = total.toFixed(2);
    }

    cashInput.addEventListener('input', calculateTotal);
    cardInput.addEventListener('input', calculateTotal);
    transferInput.addEventListener('input', calculateTotal);
    otherInput.addEventListener('input', calculateTotal);
});
</script>
@endsection

