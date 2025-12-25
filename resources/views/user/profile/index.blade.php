@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-user"></i> Thông tin cá nhân</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Cập nhật thông tin</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="{{ old('phone', $user->phone) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">CCCD/CMND</label>
                                <input type="text" name="cccd" class="form-control" 
                                       value="{{ old('cccd', $user->cccd) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="birth_date" class="form-control" 
                                       value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="address" class="form-control" 
                                       value="{{ old('address', $user->address) }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Đổi mật khẩu</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại *</label>
                            <input type="password" name="current_password" 
                                   class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới *</label>
                            <input type="password" name="password" 
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-5x text-muted mb-3"></i>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                    <hr>
                    <div class="text-start">
                        <p><i class="fas fa-calendar"></i> Tham gia: {{ $user->created_at->format('d/m/Y') }}</p>
                        <p><i class="fas fa-calendar-check"></i> Tổng đặt phòng: {{ $user->bookings->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

