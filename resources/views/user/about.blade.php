@extends('layouts.app')

@section('title', 'Giới thiệu')

@section('content')
<div class="page-header">
    <div class="container text-center text-white">
        <h1 class="display-4 mb-3"><i class="fas fa-hotel"></i> Giới thiệu về chúng tôi</h1>
        <p class="lead">Khách sạn sang trọng với dịch vụ đẳng cấp</p>
    </div>
</div>

<div class="container py-5">
    <!-- Giới thiệu chung -->
    <div class="row mb-5">
        <div class="col-lg-6">
            <h2 class="mb-4">Chào mừng đến với khách sạn của chúng tôi</h2>
            <p class="lead text-muted">
                Chúng tôi tự hào là một trong những khách sạn hàng đầu, mang đến cho quý khách những trải nghiệm nghỉ dưỡng tuyệt vời nhất.
            </p>
            <p>
                Với hơn 10 năm kinh nghiệm trong ngành dịch vụ khách sạn, chúng tôi luôn đặt sự hài lòng của khách hàng lên hàng đầu. 
                Mỗi phòng đều được thiết kế tinh tế, trang bị đầy đủ tiện nghi hiện đại để đảm bảo quý khách có một kỳ nghỉ thoải mái và đáng nhớ.
            </p>
            <p>
                Đội ngũ nhân viên chuyên nghiệp, nhiệt tình luôn sẵn sàng phục vụ quý khách 24/7. 
                Chúng tôi cam kết mang đến dịch vụ tốt nhất với giá cả hợp lý nhất.
            </p>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h4 class="mb-4"><i class="fas fa-star text-warning"></i> Điểm nổi bật</h4>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Vị trí đắc địa:</strong> Nằm ở trung tâm thành phố, dễ dàng di chuyển
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Phòng nghỉ sang trọng:</strong> Đầy đủ tiện nghi, không gian rộng rãi
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Dịch vụ đẳng cấp:</strong> Nhà hàng, spa, gym, hồ bơi
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>An ninh 24/7:</strong> Hệ thống bảo vệ chuyên nghiệp
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>WiFi miễn phí:</strong> Kết nối internet tốc độ cao
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Đỗ xe miễn phí:</strong> Bãi đỗ xe rộng rãi, an toàn
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tiện ích -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5"><i class="fas fa-concierge-bell"></i> Tiện ích & Dịch vụ</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-utensils fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Nhà hàng</h5>
                    <p class="card-text text-muted">
                        Phục vụ các món ăn đa dạng từ ẩm thực Việt Nam đến quốc tế
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-swimming-pool fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Hồ bơi</h5>
                    <p class="card-text text-muted">
                        Hồ bơi ngoài trời với view đẹp, phục vụ 24/7
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-dumbbell fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Phòng Gym</h5>
                    <p class="card-text text-muted">
                        Phòng tập gym hiện đại với đầy đủ thiết bị
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-spa fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Spa & Massage</h5>
                    <p class="card-text text-muted">
                        Dịch vụ spa và massage thư giãn chuyên nghiệp
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-wifi fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">WiFi miễn phí</h5>
                    <p class="card-text text-muted">
                        Kết nối internet tốc độ cao trong toàn bộ khách sạn
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-parking fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Đỗ xe miễn phí</h5>
                    <p class="card-text text-muted">
                        Bãi đỗ xe rộng rãi, an toàn và miễn phí cho khách
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin liên hệ -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-5 text-center">
                    <h2 class="mb-4"><i class="fas fa-phone-alt"></i> Liên hệ với chúng tôi</h2>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                            <h5>Địa chỉ</h5>
                            <p class="mb-0">123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-phone fa-2x mb-2"></i>
                            <h5>Điện thoại</h5>
                            <p class="mb-0">Hotline: 1900 1234</p>
                            <p class="mb-0">Điện thoại: (028) 1234 5678</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-envelope fa-2x mb-2"></i>
                            <h5>Email</h5>
                            <p class="mb-0">info@hotelmanagement.com</p>
                            <p class="mb-0">booking@hotelmanagement.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 0;
        margin-bottom: 50px;
    }
    .card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush

