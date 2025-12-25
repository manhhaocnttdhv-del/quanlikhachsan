@extends('layouts.admin')

@section('title', 'Chỉnh sửa khách hàng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit"></i> Chỉnh sửa khách hàng</h2>
    <a href="{{ route('admin.employee.customers.show', $customer->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.employee.customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Tên khách hàng</label>
                        <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
                        <small class="text-muted">Không thể thay đổi tên khách hàng</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $customer->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $customer->phone) }}"
                               placeholder="Nhập số điện thoại">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.employee.customers.show', $customer->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Lưu ý</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i> Bạn chỉ có thể cập nhật email và số điện thoại của khách hàng.
                </p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i> Tên khách hàng không thể thay đổi.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

