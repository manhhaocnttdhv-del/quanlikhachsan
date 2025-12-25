@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Quên mật khẩu</h3>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p class="text-muted mb-4">Nhập email của bạn để nhận link đặt lại mật khẩu.</p>
                    
                    <form method="POST" action="{{ route('user.password.email') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-paper-plane"></i> Gửi link đặt lại mật khẩu
                        </button>

                        <div class="text-center">
                            <p class="mb-0">
                                <a href="{{ route('user.login') }}" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i> Quay lại đăng nhập
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

