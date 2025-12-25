@extends('layouts.admin')

@section('title', 'Tìm phòng trống')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-search"></i> Tìm phòng trống</h2>
    <a href="{{ route('admin.employee.rooms.availability') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.rooms.search') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Ngày check-in</label>
                <input type="date" name="check_in_date" class="form-control" 
                       value="{{ $validated['check_in_date'] ?? '' }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ngày check-out</label>
                <input type="date" name="check_out_date" class="form-control" 
                       value="{{ $validated['check_out_date'] ?? '' }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Loại phòng</label>
                <select name="room_type" class="form-select">
                    <option value="">Tất cả</option>
                    @php
                        $roomTypes = \App\Models\Room::distinct()->pluck('room_type');
                    @endphp
                    @foreach($roomTypes as $type)
                        <option value="{{ $type }}" {{ (isset($validated['room_type']) && $validated['room_type'] == $type) ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Số người</label>
                <input type="number" name="capacity" class="form-control" 
                       value="{{ $validated['capacity'] ?? '' }}" min="1" placeholder="Tối thiểu">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($availableRooms))
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            Kết quả tìm kiếm: {{ $availableRooms->count() }} phòng trống
            <br>
            <small class="text-muted">
                Từ {{ \Carbon\Carbon::parse($checkInDate)->format('d/m/Y') }} 
                đến {{ \Carbon\Carbon::parse($checkOutDate)->format('d/m/Y') }}
            </small>
        </h5>
    </div>
    <div class="card-body">
        @if($availableRooms->count() > 0)
            <div class="row">
                @foreach($availableRooms as $room)
                    @php
                        $otherActiveShift = \App\Models\Shift::where('shift_date', \Carbon\Carbon::today())
                            ->where('status', 'active')
                            ->where('admin_id', '!=', auth('admin')->id())
                            ->first();
                    @endphp
                    <div class="col-md-4 mb-3">
                        @if($otherActiveShift)
                            <div class="text-decoration-none" style="opacity: 0.6; cursor: not-allowed;" title="Nhân viên khác đang làm việc. Không thể tạo booking.">
                        @else
                            <a href="{{ route('admin.employee.bookings.create', ['room_id' => $room->id, 'check_in_date' => $checkInDate->format('Y-m-d'), 'check_out_date' => $checkOutDate->format('Y-m-d')]) }}" 
                               class="text-decoration-none">
                        @endif
                            <div class="card h-100 room-card" style="cursor: pointer; transition: all 0.2s;" 
                                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" 
                                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <strong>{{ $room->room_number }}</strong> - {{ $room->room_type }}
                                        </h5>
                                        <span class="badge bg-success">Trống</span>
                                    </div>
                                    <hr>
                                    <div class="mb-2">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <strong class="text-primary fs-5">{{ number_format($room->price_per_night) }} VNĐ</strong>
                                        <small class="text-muted">/đêm</small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-users text-info"></i>
                                        <small class="text-muted">Sức chứa: {{ $room->capacity }} người</small>
                                    </div>
                                    @if($room->amenities && count($room->amenities) > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-star text-warning"></i>
                                            {{ implode(', ', array_slice($room->amenities, 0, 2)) }}
                                            @if(count($room->amenities) > 2)
                                                ...
                                            @endif
                                        </small>
                                    </div>
                                    @endif
                                    <div class="mt-3">
                                        <button class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-calendar-plus"></i> Đặt phòng ngay
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @if($otherActiveShift)
                            </div>
                        @else
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-door-closed fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có phòng trống trong khoảng thời gian này</p>
            </div>
        @endif
    </div>
</div>
@endif
@endsection

