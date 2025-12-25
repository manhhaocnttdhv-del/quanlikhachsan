<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Quản lý Khách sạn')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }
        
        body {
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar.collapsed .sidebar-brand,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .shift-info {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-brand i {
            font-size: 1.8rem;
        }
        
        .user-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-info .user-name {
            color: #fff;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .user-info .user-role {
            font-size: 0.85rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 12px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .sidebar .nav-link span {
            flex: 1;
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #2a5298;
            border: none;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle:hover {
            background: #1e3c72;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            background-color: #f8f9fa;
            min-height: 100vh;
            transition: all 0.3s ease;
            padding: 20px;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .logout-btn {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.3);
            color: #fff !important;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }
        }
        
        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="fas fa-hotel"></i>
                <span>Admin Panel</span>
            </a>
        </div>

        <div class="user-info px-3">
            <div class="user-name">{{ auth('admin')->user()->name }}</div>
            <div class="user-role">
                <span class="badge bg-{{ auth('admin')->user()->role === 'admin' ? 'danger' : (auth('admin')->user()->role === 'manager' ? 'warning' : 'info') }}">
                    @if(auth('admin')->user()->role === 'admin')
                        Administrator
                    @elseif(auth('admin')->user()->role === 'manager')
                        Manager
                    @else
                        Nhân viên
                    @endif
                </span>
            </div>
        </div>

        @if(auth('admin')->user()->isEmployee())
            @php
                $currentShift = \App\Models\Shift::where('admin_id', auth('admin')->id())
                    ->where('shift_date', \Carbon\Carbon::today())
                    ->where('status', 'active')
                    ->first();
                
                $otherActiveShift = \App\Models\Shift::where('shift_date', \Carbon\Carbon::today())
                    ->where('status', 'active')
                    ->where('admin_id', '!=', auth('admin')->id())
                    ->with('admin')
                    ->first();
            @endphp
            
            <div class="shift-info px-3 mt-2 mb-2">
                @if($currentShift)
                    <div class="shift-status-card bg-success bg-opacity-20 border border-success rounded p-2 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-success me-2"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white small mb-1">
                                    <i class="fas fa-check-circle me-1"></i>Đang làm ca
                                </div>
                                <div class="text-white-50 small">
                                    <div><strong>{{ $currentShift->getShiftTypeName() }}</strong></div>
                                    <div>{{ $currentShift->start_time }} - {{ $currentShift->end_time }}</div>
                                    <div class="mt-1" style="font-size: 0.75rem;">
                                        <i class="fas fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($currentShift->shift_date)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($otherActiveShift)
                    <div class="shift-status-card bg-warning bg-opacity-20 border border-warning rounded p-2 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white small mb-1">
                                    <i class="fas fa-user-clock me-1"></i>Nhân viên khác đang làm việc
                                </div>
                                <div class="text-white-50 small">
                                    <div><strong>{{ $otherActiveShift->admin->name }}</strong></div>
                                    <div>{{ $otherActiveShift->getShiftTypeName() }}</div>
                                    <div class="mt-1" style="font-size: 0.75rem;">
                                        {{ $otherActiveShift->start_time }} - {{ $otherActiveShift->end_time }}
                                    </div>
                                    <div class="mt-1 text-warning" style="font-size: 0.7rem;">
                                        <i class="fas fa-ban me-1"></i>Không thể tạo booking
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="shift-status-card bg-secondary bg-opacity-20 border border-secondary rounded p-2 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-secondary me-2"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-white small mb-1">
                                    <i class="fas fa-pause-circle me-1"></i>Chưa có ca active
                                </div>
                                <div class="text-white-50 small">
                                    Vui lòng đăng nhập lại hoặc liên hệ quản lý
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <ul class="nav flex-column mt-3" style="flex: 1; display: flex; flex-direction: column;">
            @if(!auth('admin')->user()->isEmployee())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}">
                    <i class="fas fa-bed"></i>
                    <span>Quản lý Phòng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Quản lý Khách hàng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Quản lý Đặt phòng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Quản lý Thanh toán</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}" href="{{ route('admin.refunds.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Quản lý Hoàn tiền</span>
                </a>
            </li>
            @endif
            
            @if(auth('admin')->user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Quản lý Nhân viên</span>
                </a>
            </li>
            
            {{-- <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}" href="{{ route('admin.shifts.index') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Phân công Ca làm việc</span>
                </a>
            </li> --}}
            
            {{-- <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.shift-reports.*') ? 'active' : '' }}" href="{{ route('admin.shift-reports.index') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Doanh thu theo Ca</span>
                </a>
            </li> --}}
            @endif
            
            @if(!auth('admin')->user()->isEmployee())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                    <i class="fas fa-star"></i>
                    <span>Quản lý Đánh giá</span>
                </a>
            </li>
            @endif
            
            @if(auth('admin')->user()->isEmployee())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employee.bookings.*') ? 'active' : '' }}" href="{{ route('admin.employee.bookings.index') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Danh sách đặt phòng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employee.checkout.*') ? 'active' : '' }}" href="{{ route('admin.employee.checkout.index') }}">
                    <i class="fas fa-door-open"></i>
                    <span>Checkout</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}" href="{{ route('admin.refunds.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Quản lý Hoàn tiền</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employee.customers.*') ? 'active' : '' }}" href="{{ route('admin.employee.customers.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Khách hàng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employee.rooms.*') ? 'active' : '' }}" href="{{ route('admin.employee.rooms.availability') }}">
                    <i class="fas fa-door-open"></i>
                    <span>Phòng trống</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employee.shifts.*') ? 'active' : '' }}" href="{{ route('admin.employee.shifts.index') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Ca làm việc</span>
                </a>
            </li>
            
            <!-- Tìm kiếm nhanh -->
            <li class="nav-item mt-3" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                <div class="px-3">
                    <form action="{{ route('admin.employee.quick-search') }}" method="GET" class="quick-search-form">
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control" 
                                   placeholder="Tìm nhanh..." 
                                   value="{{ request('q') }}"
                                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                            <button type="submit" class="btn btn-outline-light" style="border-color: rgba(255,255,255,0.2);">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </li>
            @endif
            
            <li class="nav-item" style="margin-top: auto; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                <button type="button" class="nav-link border-0 bg-transparent w-100 text-start" onclick="toggleSidebarCollapse()" title="Thu gọn sidebar">
                    <i class="fas fa-chevron-left"></i>
                    <span>Thu gọn</span>
                </button>
            </li>
            
            <li class="nav-item">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link logout-btn border-0 w-100 text-start">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('errors'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach(session('errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Toggle sidebar collapse (desktop)
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Update collapse button icon
        const collapseBtn = document.querySelector('[onclick="toggleSidebarCollapse()"]');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('collapsed')) {
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                } else {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                }
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>

