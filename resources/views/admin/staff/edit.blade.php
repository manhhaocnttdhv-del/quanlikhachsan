@extends('layouts.admin')

@section('title', 'Sửa nhân viên #' . $staff->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit"></i> Sửa nhân viên #{{ $staff->id }}</h2>
    <a href="{{ route('admin.staff.show', $staff->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Cập nhật thông tin nhân viên</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.staff.update', $staff->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $staff->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $staff->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $staff->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vai trò *</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="admin" {{ old('role', $staff->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="employee" {{ old('role', $staff->role) == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <a href="{{ route('admin.staff.show', $staff->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Mã nhân viên:</label>
                    <p class="fw-bold mb-0">#{{ $staff->id }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Ngày tạo:</label>
                    <p class="fw-bold mb-0">{{ $staff->created_at ? $staff->created_at->format('d/m/Y H:i') : '-' }}</p>
                </div>

                @if($staff->updated_at && $staff->updated_at != $staff->created_at)
                    <div class="mb-3">
                        <label class="text-muted small">Cập nhật lần cuối:</label>
                        <p class="fw-bold mb-0">{{ $staff->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

