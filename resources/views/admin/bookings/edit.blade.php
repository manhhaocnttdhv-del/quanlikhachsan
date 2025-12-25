@extends('layouts.admin')

@section('title', 'Sửa đặt phòng #' . $booking->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit"></i> Sửa đặt phòng #{{ $booking->id }}</h2>
    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Trạng thái *</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="checked_in" {{ old('status', $booking->status) == 'checked_in' ? 'selected' : '' }}>Đã nhận phòng</option>
                            <option value="checked_out" {{ old('status', $booking->status) == 'checked_out' ? 'selected' : '' }}>Đã trả phòng</option>
                            <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Khi chuyển sang "Đã nhận phòng", phòng sẽ tự động chuyển trạng thái "Đã đặt".
                            Khi "Đã trả phòng", phòng sẽ chuyển về "Trống".
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yêu cầu đặc biệt</label>
                        <textarea name="special_requests" class="form-control @error('special_requests') is-invalid @enderror" 
                                  rows="3">{{ old('special_requests', $booking->special_requests) }}</textarea>
                        @error('special_requests')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đặt phòng</h5>
            </div>
            <div class="card-body">
                <p><strong>Khách hàng:</strong><br>{{ $booking->user->name }}</p>
                <p><strong>Phòng:</strong><br>{{ $booking->room->room_number }} - {{ $booking->room->room_type }}</p>
                <p><strong>Ngày nhận:</strong><br>
                    {{ $booking->check_in_date->format('d/m/Y') }}
                    @if($booking->check_in_time)
                        <span class="text-muted">({{ substr($booking->check_in_time, 0, 5) }})</span>
                    @endif
                </p>
                <p><strong>Ngày trả:</strong><br>
                    {{ $booking->check_out_date->format('d/m/Y') }}
                    @if($booking->check_out_time)
                        <span class="text-muted">({{ substr($booking->check_out_time, 0, 5) }})</span>
                    @endif
                </p>
                <p><strong>Số người:</strong><br>{{ $booking->number_of_guests }} người</p>
                <p class="mb-0"><strong>Tổng tiền:</strong><br><span class="text-primary fs-5">{{ number_format($booking->total_price) }} VNĐ</span></p>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <strong>Lưu ý:</strong>
            <ul class="mb-0 mt-2 small">
                <li>Không thể thay đổi khách hàng và phòng</li>
                <li>Để thay đổi ngày, hãy tạo đặt phòng mới</li>
                <li>Trạng thái ảnh hưởng đến trạng thái phòng</li>
            </ul>
        </div>
    </div>
</div>
@endsection

