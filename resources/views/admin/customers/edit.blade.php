@extends('layouts.admin')

@section('title', 'Sửa khách hàng #' . $customer->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit"></i> Sửa khách hàng #{{ $customer->id }}</h2>
    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Cập nhật thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $customer->phone) }}" 
                                   placeholder="VD: 0901234567">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">CCCD/CMND</label>
                            <input type="text" name="cccd" class="form-control @error('cccd') is-invalid @enderror" 
                                   value="{{ old('cccd', $customer->cccd) }}" 
                                   placeholder="VD: 001234567890">
                            @error('cccd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" 
                               value="{{ old('address', $customer->address) }}" 
                               placeholder="VD: 123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" 
                               value="{{ old('birth_date', $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '') }}">
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary">
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
                <h5 class="mb-0">Thông tin hiện tại</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Tên khách hàng:</label>
                    <p class="fw-bold mb-0">{{ $customer->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email:</label>
                    <p class="mb-0">{{ $customer->email }}</p>
                </div>
                @if($customer->phone)
                <div class="mb-3">
                    <label class="text-muted small">Số điện thoại:</label>
                    <p class="mb-0">{{ $customer->phone }}</p>
                </div>
                @endif
                @if($customer->address)
                <div class="mb-3">
                    <label class="text-muted small">Địa chỉ:</label>
                    <p class="mb-0">{{ $customer->address }}</p>
                </div>
                @endif
                @if($customer->cccd)
                <div class="mb-3">
                    <label class="text-muted small">CCCD/CMND:</label>
                    <p class="mb-0">{{ $customer->cccd }}</p>
                </div>
                @endif
                @if($customer->birth_date)
                <div class="mb-3">
                    <label class="text-muted small">Ngày sinh:</label>
                    <p class="mb-0">{{ $customer->birth_date->format('d/m/Y') }}</p>
                </div>
                @endif
                <div class="mb-3">
                    <label class="text-muted small">Ngày đăng ký:</label>
                    <p class="mb-0">{{ $customer->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

