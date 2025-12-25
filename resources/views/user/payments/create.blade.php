@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-credit-card"></i> Thanh toán</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Chọn phương thức thanh toán</h5>

                    <form action="{{ route('user.payments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="list-group mb-4">
                            {{-- <label class="list-group-item">
                                <input class="form-check-input me-3" type="radio" name="payment_method" 
                                       value="cash" {{ old('payment_method') == 'cash' ? 'checked' : '' }} required>
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                        <strong class="ms-2">Tiền mặt trực tiếp tại khách sạn</strong>
                                    </div>
                                    <small class="text-muted">Thanh toán trực tiếp khi đến khách sạn</small>
                                </div>
                            </label> --}}
                            
                            <label class="list-group-item">
                                <input class="form-check-input me-3" type="radio" name="payment_method" 
                                       value="bank_transfer_qr" {{ old('payment_method', 'bank_transfer_qr') == 'bank_transfer_qr' ? 'checked' : '' }} required>
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-qrcode fa-2x text-primary"></i>
                                        <strong class="ms-2">QR Chuyển khoản</strong>
                                    </div>
                                    <small class="text-muted">Quét QR code để chuyển khoản</small>
                                </div>
                            </label>
                        </div>

                        @error('payment_method')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Xác nhận thanh toán
                            </button>
                            <a href="{{ route('user.bookings.show', $booking->id) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chi tiết thanh toán</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Phòng {{ $booking->room->room_number }}</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Loại phòng:</span>
                        <strong>{{ $booking->room->room_type }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày nhận:</span>
                        <strong>{{ $booking->check_in_date->format('d/m/Y') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày trả:</span>
                        <strong>{{ $booking->check_out_date->format('d/m/Y') }}</strong>
                    </div>
                    @php
                        $checkIn  = Carbon\Carbon::parse($booking->check_in_date);
                        $checkOut = Carbon\Carbon::parse($booking->check_out_date);
                        $nights  = $checkIn->diffInDays($checkOut);
                        if ($checkOut->format('H:i') >= $checkIn->format('H:i')) {
                            $nights += 1;
                        }
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Số đêm:</span>
                        <strong>{{ $nights }} đêm</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Số người:</span>
                        <strong>{{ $booking->number_of_guests }} người</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Giá/đêm:</span>
                        <strong>{{ number_format($booking->room->price_per_night) }} VNĐ</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Tổng cộng:</h5>
                        <h4 class="text-primary mb-0">{{ number_format($booking->total_price) }} VNĐ</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .list-group-item {
        cursor: pointer;
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .list-group-item:has(input:checked) {
        background-color: #e7f3ff;
        border-color: #0d6efd;
    }
</style>
@endpush
@endsection

