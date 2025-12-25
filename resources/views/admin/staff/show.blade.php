@extends('layouts.admin')

@section('title', 'Chi tiết nhân viên #' . $staff->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-tie"></i> Chi tiết nhân viên #{{ $staff->id }}</h2>
    <div>
        <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin nhân viên</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã nhân viên:</label>
                        <p class="fw-bold">#{{ $staff->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Vai trò:</label>
                        <p class="mb-0">
                            @if($staff->role == 'admin')
                                <span class="badge bg-danger fs-6">Admin</span>
                            @else
                                <span class="badge bg-primary fs-6">Manager</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Họ và tên:</label>
                        <p class="fw-bold">{{ $staff->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email:</label>
                        <p class="fw-bold">{{ $staff->email }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Số điện thoại:</label>
                        <p class="fw-bold">{{ $staff->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ngày tạo:</label>
                        <p class="fw-bold">{{ $staff->created_at ? $staff->created_at->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                </div>

                @if($staff->updated_at && $staff->updated_at != $staff->created_at)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Cập nhật lần cuối:</label>
                            <p class="fw-bold">{{ $staff->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hành động</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-edit"></i> Sửa thông tin
                </a>

                @if(auth('admin')->id() !== $staff->id)
                    <form action="{{ route('admin.staff.destroy', $staff->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">
                            <i class="fas fa-trash"></i> Xóa nhân viên
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-secondary w-100" disabled>
                        <i class="fas fa-info-circle"></i> Không thể xóa chính mình
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

