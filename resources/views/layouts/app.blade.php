<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống Quản lý Khách sạn')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            flex: 1;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: white !important;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-link {
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }
        .nav-link:hover {
            color: #667eea !important;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        .nav-link:hover::after {
            width: 80%;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        footer {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-top: auto;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        /* Card improvements */
        .card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        /* Button improvements */
        .btn {
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        /* Form improvements */
        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        /* Badge improvements */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        /* Loading animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-hotel"></i> Hotel Global
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rooms.index') }}">Phòng</a>
                    </li>
                    
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.bookings.index') }}">Đặt phòng của tôi</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('user.dashboard.index') }}"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.profile.index') }}"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('user.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.login') }}">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.register') }}">Đăng ký</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 text-md-start">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-hotel"></i> Hotel Global
                    </h5>
                    <p class="small opacity-75">Trải nghiệm nghỉ dưỡng đẳng cấp tại khách sạn 5 sao, với các tiện nghi hiện đại và dịch vụ hoàn hảo, mang đến sự thư giãn tối đa cho kỳ nghỉ của bạn.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold mb-3">Liên hệ</h6>
                    <p class="small opacity-75 mb-1"><i class="fas fa-phone me-2"></i> 0123-456-789</p>
                    <p class="small opacity-75 mb-1"><i class="fas fa-envelope me-2"></i> info@hotel.com</p>
                    <p class="small opacity-75 mb-0"><i class="fas fa-map-marker-alt me-2"></i> Vinh, Việt Nam</p>
                </div>
                <div class="col-md-4 mb-3 text-md-end">
                    <h6 class="fw-bold mb-3">Theo dõi chúng tôi</h6>
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                </div>
            </div>
            <hr class="my-3 opacity-25">
            <p class="small opacity-75 mb-0">&copy; {{ date('Y') }} Hotel Global System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

