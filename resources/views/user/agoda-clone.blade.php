@extends('layouts.app')

@section('title', 'Mường Thanh Sông Lam Hotel - Vinh')

@push('styles')
<style>
    /* Agoda Style Variables */
    :root {
        --agoda-primary: #1a73e8;
        --agoda-secondary: #ea4335;
        --agoda-success: #34a853;
        --agoda-warning: #fbbc04;
        --agoda-text: #333;
        --agoda-text-light: #666;
        --agoda-border: #e0e0e0;
        --agoda-bg: #f5f5f5;
    }

    body {
        background-color: #fff;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        height: 500px;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        padding: 40px 0 20px;
        color: white;
    }

    .hotel-name {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .hotel-location {
        font-size: 1.1rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Rating Section */
    .rating-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .rating-score {
        font-size: 3rem;
        font-weight: 700;
        color: var(--agoda-primary);
    }

    .rating-badge {
        background: var(--agoda-success);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    /* Photo Gallery */
    .photo-gallery {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 8px;
        height: 400px;
        margin-bottom: 30px;
        border-radius: 8px;
        overflow: hidden;
    }

    .gallery-main {
        grid-row: 1 / 3;
    }

    .gallery-item {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .gallery-item:hover {
        transform: scale(1.05);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 10px;
        text-align: center;
        font-weight: 600;
    }

    /* Booking Widget */
    .booking-widget {
        position: sticky;
        top: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 25px;
        margin-bottom: 30px;
    }

    .price-display {
        margin-bottom: 20px;
    }

    .price-label {
        font-size: 0.9rem;
        color: var(--agoda-text-light);
        margin-bottom: 5px;
    }

    .price-amount {
        font-size: 2rem;
        font-weight: 700;
        color: var(--agoda-primary);
    }

    .price-period {
        font-size: 1rem;
        color: var(--agoda-text-light);
    }

    .booking-form {
        margin-top: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--agoda-text);
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border: 2px solid var(--agoda-border);
        border-radius: 8px;
        padding: 12px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        border-color: var(--agoda-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
    }

    .btn-book {
        width: 100%;
        background: var(--agoda-primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 15px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 10px;
    }

    .btn-book:hover {
        background: #1557b0;
    }

    /* Room Types */
    .room-types-section {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 25px;
        color: var(--agoda-text);
    }

    .room-card {
        border: 2px solid var(--agoda-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .room-card:hover {
        border-color: var(--agoda-primary);
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
    }

    .room-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .room-name {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--agoda-text);
    }

    .room-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--agoda-primary);
    }

    .room-features {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: var(--agoda-text-light);
        font-size: 0.9rem;
    }

    .feature-item i {
        color: var(--agoda-success);
    }

    /* Amenities Section */
    .amenities-section {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .amenity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
    }

    .amenity-icon {
        width: 40px;
        height: 40px;
        background: #f0f7ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--agoda-primary);
        font-size: 1.2rem;
    }

    /* Reviews Section */
    .reviews-section {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .review-card {
        border-bottom: 1px solid var(--agoda-border);
        padding: 20px 0;
    }

    .review-card:last-child {
        border-bottom: none;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }

    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .reviewer-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--agoda-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .reviewer-name {
        font-weight: 600;
        color: var(--agoda-text);
    }

    .review-date {
        color: var(--agoda-text-light);
        font-size: 0.9rem;
    }

    .review-rating {
        color: var(--agoda-warning);
    }

    /* Map Section */
    .map-section {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .map-container {
        height: 400px;
        border-radius: 8px;
        overflow: hidden;
        background: var(--agoda-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--agoda-text-light);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hotel-name {
            font-size: 1.8rem;
        }

        .photo-gallery {
            grid-template-columns: 1fr;
            grid-template-rows: auto;
            height: auto;
        }

        .gallery-main {
            grid-row: 1;
        }

        .booking-widget {
            position: relative;
            top: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <div class="hero-section">
        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=80" alt="Hotel" class="hero-image">
        <div class="hero-overlay">
            <div class="container">
                <h1 class="hotel-name">Mường Thanh Sông Lam Hotel</h1>
                <div class="hotel-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Vinh, Nghệ An, Việt Nam</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Rating Section -->
                <div class="rating-section">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="rating-score">8.5</div>
                            <div class="mt-2">
                                <span class="rating-badge">Tuyệt vời</span>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-2">
                                <strong>Dựa trên 1,234 đánh giá</strong>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                                <span class="ms-2">4.5 / 5</span>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-check-circle text-success"></i> Xác nhận nhanh chóng
                                <span class="ms-3"><i class="fas fa-shield-alt text-primary"></i> Thanh toán an toàn</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Gallery -->
                <div class="photo-gallery">
                    <div class="gallery-item gallery-main">
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80" alt="Hotel">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=400&q=80" alt="Room">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400&q=80" alt="Bathroom">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=400&q=80" alt="Pool">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=400&q=80" alt="Restaurant">
                        <div class="gallery-overlay">
                            <i class="fas fa-images"></i> Xem tất cả ảnh
                        </div>
                    </div>
                </div>

                <!-- Room Types Section -->
                <div class="room-types-section">
                    <h2 class="section-title">Chọn loại phòng</h2>
                    
                    <div class="room-card">
                        <div class="room-header">
                            <div>
                                <div class="room-name">Phòng Deluxe</div>
                                <div class="small text-muted mt-1">35 m² • Giường đôi hoặc 2 giường đơn</div>
                            </div>
                            <div class="text-end">
                                <div class="room-price">1,500,000₫</div>
                                <div class="small text-muted">/ đêm</div>
                            </div>
                        </div>
                        <div class="room-features">
                            <div class="feature-item">
                                <i class="fas fa-wifi"></i>
                                <span>WiFi miễn phí</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>TV màn hình phẳng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bath"></i>
                                <span>Phòng tắm riêng</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary">Chọn phòng</button>
                    </div>

                    <div class="room-card">
                        <div class="room-header">
                            <div>
                                <div class="room-name">Phòng Suite</div>
                                <div class="small text-muted mt-1">50 m² • Giường King Size</div>
                            </div>
                            <div class="text-end">
                                <div class="room-price">2,500,000₫</div>
                                <div class="small text-muted">/ đêm</div>
                            </div>
                        </div>
                        <div class="room-features">
                            <div class="feature-item">
                                <i class="fas fa-wifi"></i>
                                <span>WiFi miễn phí</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>TV màn hình phẳng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bath"></i>
                                <span>Bồn tắm</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-couch"></i>
                                <span>Khu vực tiếp khách</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary">Chọn phòng</button>
                    </div>

                    <div class="room-card">
                        <div class="room-header">
                            <div>
                                <div class="room-name">Phòng Family</div>
                                <div class="small text-muted mt-1">60 m² • 2 giường đôi</div>
                            </div>
                            <div class="text-end">
                                <div class="room-price">3,000,000₫</div>
                                <div class="small text-muted">/ đêm</div>
                            </div>
                        </div>
                        <div class="room-features">
                            <div class="feature-item">
                                <i class="fas fa-wifi"></i>
                                <span>WiFi miễn phí</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>TV màn hình phẳng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bath"></i>
                                <span>Phòng tắm riêng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-users"></i>
                                <span>Phù hợp gia đình</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary">Chọn phòng</button>
                    </div>
                </div>

                <!-- Amenities Section -->
                <div class="amenities-section">
                    <h2 class="section-title">Tiện nghi khách sạn</h2>
                    <div class="amenities-grid">
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-swimming-pool"></i>
                            </div>
                            <div>
                                <strong>Hồ bơi</strong>
                                <div class="small text-muted">Hồ bơi ngoài trời</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div>
                                <strong>Nhà hàng</strong>
                                <div class="small text-muted">Phục vụ bữa sáng</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div>
                                <strong>Phòng gym</strong>
                                <div class="small text-muted">24/7</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div>
                                <strong>WiFi miễn phí</strong>
                                <div class="small text-muted">Toàn bộ khách sạn</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-parking"></i>
                            </div>
                            <div>
                                <strong>Bãi đỗ xe</strong>
                                <div class="small text-muted">Miễn phí</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <div>
                                <strong>Dịch vụ lễ tân</strong>
                                <div class="small text-muted">24/7</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-spa"></i>
                            </div>
                            <div>
                                <strong>Spa & Massage</strong>
                                <div class="small text-muted">Dịch vụ spa</div>
                            </div>
                        </div>
                        <div class="amenity-item">
                            <div class="amenity-icon">
                                <i class="fas fa-business-time"></i>
                            </div>
                            <div>
                                <strong>Phòng họp</strong>
                                <div class="small text-muted">Có sẵn</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="reviews-section">
                    <h2 class="section-title">Đánh giá từ khách hàng</h2>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">NT</div>
                                <div>
                                    <div class="reviewer-name">Nguyễn Thành</div>
                                    <div class="review-date">Tháng 12, 2024</div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="mt-3">Khách sạn rất đẹp, phòng sạch sẽ, nhân viên thân thiện. Vị trí thuận tiện, gần trung tâm thành phố. Bữa sáng rất ngon và đa dạng. Sẽ quay lại lần sau!</p>
                    </div>

                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">LM</div>
                                <div>
                                    <div class="reviewer-name">Lê Minh</div>
                                    <div class="review-date">Tháng 11, 2024</div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>
                        <p class="mt-3">Trải nghiệm tốt, phòng rộng rãi và thoải mái. Hồ bơi rất đẹp. Chỉ có một điểm nhỏ là WiFi hơi chậm vào buổi tối.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">PT</div>
                                <div>
                                    <div class="reviewer-name">Phạm Thảo</div>
                                    <div class="review-date">Tháng 10, 2024</div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="mt-3">Tuyệt vời! Dịch vụ xuất sắc, phòng view đẹp. Nhân viên rất chuyên nghiệp và nhiệt tình. Giá cả hợp lý so với chất lượng dịch vụ.</p>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="map-section">
                    <h2 class="section-title">Vị trí</h2>
                    <div class="map-container">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-3x mb-3 text-muted"></i>
                            <div>Bản đồ sẽ được hiển thị tại đây</div>
                            <div class="small text-muted mt-2">Vinh, Nghệ An, Việt Nam</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Booking Widget -->
            <div class="col-lg-4">
                <div class="booking-widget">
                    <div class="price-display">
                        <div class="price-label">Giá mỗi đêm từ</div>
                        <div class="price-amount">1,500,000₫</div>
                        <div class="price-period">+ Thuế & Phí</div>
                    </div>

                    <div class="booking-form">
                        <div class="form-group">
                            <label class="form-label">Ngày nhận phòng</label>
                            <input type="date" class="form-control" id="checkIn">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ngày trả phòng</label>
                            <input type="date" class="form-control" id="checkOut">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Số khách</label>
                            <select class="form-control">
                                <option>1 khách</option>
                                <option>2 khách</option>
                                <option>3 khách</option>
                                <option>4 khách</option>
                                <option>5+ khách</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Số phòng</label>
                            <select class="form-control">
                                <option>1 phòng</option>
                                <option>2 phòng</option>
                                <option>3 phòng</option>
                            </select>
                        </div>

                        <button class="btn-book">
                            <i class="fas fa-calendar-check me-2"></i>
                            Kiểm tra giá & Đặt phòng
                        </button>

                        <div class="mt-3 text-center small text-muted">
                            <i class="fas fa-shield-alt text-success me-1"></i>
                            Đặt phòng miễn phí hủy
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="small">
                        <div class="mb-2">
                            <strong><i class="fas fa-check-circle text-success me-2"></i>Xác nhận tức thì</strong>
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-lock text-primary me-2"></i>Thanh toán an toàn</strong>
                        </div>
                        <div>
                            <strong><i class="fas fa-headset text-info me-2"></i>Hỗ trợ 24/7</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('checkIn').setAttribute('min', today);
    document.getElementById('checkOut').setAttribute('min', today);

    // Update check-out minimum date when check-in changes
    document.getElementById('checkIn').addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        const minCheckOut = checkInDate.toISOString().split('T')[0];
        document.getElementById('checkOut').setAttribute('min', minCheckOut);
    });

    // Gallery click handler
    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function() {
            // You can implement a lightbox here
            console.log('Gallery item clicked');
        });
    });
</script>
@endpush

