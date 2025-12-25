@extends('layouts.admin')

@section('title', 'Phòng trống')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-door-open"></i> Phòng trống</h2>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.rooms.availability') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Chọn ngày</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.employee.rooms.search') }}" class="btn btn-info">
                    <i class="fas fa-calendar-alt"></i> Tìm theo khoảng thời gian
                </a>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('admin.employee.rooms.calendar') }}" class="btn btn-success">
                    <i class="fas fa-calendar"></i> Xem lịch phòng
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Phòng trống -->
    <div class="col-md-4 mb-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-door-open"></i> Phòng trống ({{ $availableRooms->count() }})
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @forelse($availableRooms as $room)
                    @php
                        $otherActiveShift = \App\Models\Shift::where('shift_date', \Carbon\Carbon::today())
                            ->where('status', 'active')
                            ->where('admin_id', '!=', auth('admin')->id())
                            ->first();
                    @endphp
                    @if($otherActiveShift)
                        <div class="text-decoration-none" style="opacity: 0.6; cursor: not-allowed;" title="Nhân viên khác đang làm việc. Không thể tạo booking.">
                    @else
                        <a href="{{ route('admin.employee.bookings.create', ['room_id' => $room->id, 'check_in_date' => $selectedDate->format('Y-m-d'), 'check_out_date' => $selectedDate->copy()->addDay()->format('Y-m-d')]) }}" 
                           class="text-decoration-none">
                    @endif
                        <div class="card mb-2 room-card" style="cursor: pointer; transition: all 0.2s;" 
                             onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)';" 
                             onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <strong>{{ $room->room_number }}</strong> - {{ $room->room_type }}
                                        </h6>
                                        <div class="mb-1">
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                            <strong class="text-primary">{{ number_format($room->price_per_night) }} VNĐ/đêm</strong>
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-users text-info"></i>
                                            <small class="text-muted">Sức chứa: {{ $room->capacity }} người</small>
                                        </div>
                                        @if($room->amenities && count($room->amenities) > 0)
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-star text-warning"></i>
                                                {{ implode(', ', array_slice($room->amenities, 0, 2)) }}
                                                @if(count($room->amenities) > 2)
                                                    ...
                                                @endif
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success mb-2">Trống</span>
                                        <br>
                                        <small class="text-primary">
                                            <i class="fas fa-arrow-right"></i> Click để đặt
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @if($otherActiveShift)
                        </div>
                    @else
                        </a>
                    @endif
                @empty
                    <p class="text-muted text-center py-4">Không có phòng trống</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Phòng đã đặt -->
    <div class="col-md-4 mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bed"></i> Phòng đã đặt ({{ $occupiedRooms->count() }})
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @forelse($occupiedRooms as $room)
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <strong>{{ $room->room_number }}</strong> - {{ $room->room_type }}
                                    </h6>
                                    <div class="mb-1">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <strong class="text-primary">{{ number_format($room->price_per_night) }} VNĐ/đêm</strong>
                                    </div>
                                    <div class="mb-1">
                                        <i class="fas fa-users text-info"></i>
                                        <small class="text-muted">Sức chứa: {{ $room->capacity }} người</small>
                                    </div>
                                    @if($room->current_booking)
                                        <hr class="my-2">
                                        <div class="mb-1">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <strong>Khách:</strong> {{ $room->current_booking->user->name }}
                                            </small>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                {{ \Carbon\Carbon::parse($room->current_booking->check_in_date)->format('d/m/Y') }} - 
                                                {{ \Carbon\Carbon::parse($room->current_booking->check_out_date)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                <span class="badge bg-info">Đã đặt</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">Không có phòng đã đặt</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Phòng bảo trì -->
    <div class="col-md-4 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-tools"></i> Phòng bảo trì ({{ $maintenanceRooms->count() }})
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @forelse($maintenanceRooms as $room)
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <strong>{{ $room->room_number }}</strong> - {{ $room->room_type }}
                                    </h6>
                                    <div class="mb-1">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <strong class="text-primary">{{ number_format($room->price_per_night) }} VNĐ/đêm</strong>
                                    </div>
                                    <div>
                                        <i class="fas fa-users text-info"></i>
                                        <small class="text-muted">Sức chứa: {{ $room->capacity }} người</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning">Bảo trì</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">Không có phòng bảo trì</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

