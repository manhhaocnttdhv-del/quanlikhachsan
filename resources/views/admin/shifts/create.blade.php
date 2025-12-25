@extends('layouts.admin')

@section('title', 'Phân công ca mới')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-plus"></i> Phân công ca mới</h2>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.shifts.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nhân viên *</label>
                    <select name="admin_id" class="form-select @error('admin_id') is-invalid @enderror" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('admin_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('admin_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày làm việc *</label>
                    <input type="date" name="shift_date" class="form-control @error('shift_date') is-invalid @enderror" 
                           value="{{ old('shift_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                    @error('shift_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ca làm việc *</label>
                    <select name="shift_type" id="shiftTypeSelect" class="form-select @error('shift_type') is-invalid @enderror" required>
                        <option value="">-- Chọn ca --</option>
                        <option value="morning" {{ old('shift_type') == 'morning' ? 'selected' : '' }}>Ca sáng (06:00 - 12:00)</option>
                        <option value="afternoon" {{ old('shift_type') == 'afternoon' ? 'selected' : '' }}>Ca trưa (12:00 - 18:00)</option>
                        <option value="evening" {{ old('shift_type') == 'evening' ? 'selected' : '' }}>Ca tối (18:00 - 24:00)</option>
                    </select>
                    @error('shift_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Giờ làm việc sẽ được tự động thiết lập theo ca đã chọn</small>
                </div>
            </div>
            
            <input type="hidden" name="start_time" id="startTimeInput">
            <input type="hidden" name="end_time" id="endTimeInput">

            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                          placeholder="Ghi chú về ca làm việc...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Phân công ca
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shiftTypeSelect = document.getElementById('shiftTypeSelect');
    const startTimeInput = document.getElementById('startTimeInput');
    const endTimeInput = document.getElementById('endTimeInput');
    
    const shiftTimes = {
        'morning': { start: '06:00', end: '12:00' },
        'afternoon': { start: '12:00', end: '18:00' },
        'evening': { start: '18:00', end: '24:00' }
    };
    
    function updateTimes() {
        const selectedType = shiftTypeSelect.value;
        if (selectedType && shiftTimes[selectedType]) {
            startTimeInput.value = shiftTimes[selectedType].start;
            endTimeInput.value = shiftTimes[selectedType].end;
        }
    }
    
    shiftTypeSelect.addEventListener('change', updateTimes);
    
    // Set initial values if old input exists
    if (shiftTypeSelect.value) {
        updateTimes();
    }
});
</script>
@endsection

