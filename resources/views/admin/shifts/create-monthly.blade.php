@extends('layouts.admin')

@section('title', 'Phân công ca theo tháng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Phân công ca theo tháng</h2>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.shifts.storeMonthly') }}" method="POST">
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
                    <label class="form-label">Tháng *</label>
                    <input type="month" name="month" class="form-control @error('month') is-invalid @enderror" 
                           value="{{ old('month', date('Y-m')) }}" min="{{ date('Y-m') }}" required>
                    @error('month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Chọn tháng cần phân công ca</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Các ca cần phân công *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="shift_types[]" value="morning" 
                               id="shift_morning" {{ in_array('morning', old('shift_types', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="shift_morning">
                            <strong>Ca sáng</strong> (06:00 - 12:00)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="shift_types[]" value="afternoon" 
                               id="shift_afternoon" {{ in_array('afternoon', old('shift_types', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="shift_afternoon">
                            <strong>Ca trưa</strong> (12:00 - 18:00)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="shift_types[]" value="evening" 
                               id="shift_evening" {{ in_array('evening', old('shift_types', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="shift_evening">
                            <strong>Ca tối</strong> (18:00 - 24:00)
                        </label>
                    </div>
                    @error('shift_types')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    @error('shift_types.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-2">Chọn ít nhất một ca. Hệ thống sẽ tự động tạo ca cho tất cả các ngày trong tháng (trừ các ngày đã qua).</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                          placeholder="Ghi chú về phân công ca...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Lưu ý:</strong> Hệ thống sẽ tự động tạo ca cho tất cả các ngày trong tháng đã chọn. 
                Nếu nhân viên đã có ca trong ngày đó, hệ thống sẽ bỏ qua để tránh trùng lặp.
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Phân công ca theo tháng
            </button>
        </form>
    </div>
</div>
@endsection

