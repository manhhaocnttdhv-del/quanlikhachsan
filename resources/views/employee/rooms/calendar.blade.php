@extends('layouts.admin')

@section('title', 'Lịch phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar"></i> Lịch phòng</h2>
    <a href="{{ route('admin.employee.rooms.availability') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.rooms.calendar') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tháng</label>
                <select name="month" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            Tháng {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Năm</label>
                <input type="number" name="year" class="form-control" value="{{ $year }}" min="2024" max="2030">
            </div>
            <div class="col-md-3">
                <label class="form-label">Phòng (tùy chọn)</label>
                <select name="room_id" class="form-select">
                    <option value="">Tất cả phòng</option>
                    @foreach($rooms as $r)
                        <option value="{{ $r->id }}" {{ $roomId == $r->id ? 'selected' : '' }}>
                            {{ $r->room_number }} - {{ $r->room_type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Xem
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        @foreach($rooms as $room)
                            <th class="text-center">{{ $room->room_number }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendarData as $dateStr => $data)
                        <tr>
                            <td>
                                <strong>{{ $data['date']->format('d/m') }}</strong><br>
                                <small class="text-muted">{{ $data['date']->format('D') }}</small>
                            </td>
                            @foreach($rooms as $room)
                                @php
                                    $roomData = $data['rooms'][$room->id] ?? null;
                                    $isOccupied = $roomData && $roomData['is_occupied'];
                                @endphp
                                <td class="text-center {{ $isOccupied ? 'bg-danger bg-opacity-25' : 'bg-success bg-opacity-25' }}" 
                                    style="min-width: 100px;">
                                    @if($isOccupied)
                                        <span class="badge bg-danger">Đã đặt</span>
                                        @if($roomData['bookings']->count() > 0)
                                            <br>
                                            <small class="text-muted">
                                                {{ $roomData['bookings']->first()->user->name }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-success">Trống</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

