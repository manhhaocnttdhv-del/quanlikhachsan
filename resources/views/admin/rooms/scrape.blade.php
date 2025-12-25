@extends('layouts.admin')

@section('title', 'Import phòng từ các trang đặt phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-download"></i> Import phòng từ các trang đặt phòng</h2>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Lưu ý:</strong> Nhiều trang web có hệ thống bảo vệ chống bot/scraper rất mạnh, có thể không scrape được. 
            <strong>Khuyến nghị sử dụng chức năng Import từ Excel/CSV</strong> để nhập thông tin phòng thủ công.
            <a href="{{ route('admin.rooms.import.form') }}" class="btn btn-sm btn-info ms-2">
                <i class="fas fa-file-import"></i> Import từ Excel/CSV
            </a>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Hướng dẫn:</strong> Dán link trang kết quả tìm kiếm hoặc trang chi tiết khách sạn từ các trang đặt phòng được hỗ trợ vào ô bên dưới. 
            Hệ thống sẽ tự động nhận diện loại website và lấy thông tin các phòng để lưu vào database.
            <br><br>
            <strong>Lưu ý:</strong> URL phải là trang chi tiết khách sạn hoặc trang tìm kiếm có kết quả phòng. 
            <span class="text-danger">Không sử dụng URL trang thành phố (city page)</span> vì trang đó chỉ hiển thị danh sách khách sạn, không có thông tin phòng cụ thể.
        </div>
        
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <strong>Hệ thống hỗ trợ các trang web sau:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Booking.com</strong> - Prefix: <code>BC-</code> ✅ Khuyến nghị</li>
                <li><strong>Agoda.com</strong> - Prefix: <code>AG-</code> ✅ Khuyến nghị</li>
                <li><strong>Traveloka</strong> - Prefix: <code>TK-</code> ✅ Khuyến nghị</li>
                <li><strong>VnTravel</strong> - Prefix: <code>VT-</code> ✅ Khuyến nghị</li>
                <li><strong>Mytour.vn</strong> - Prefix: <code>MT-</code> ✅ Khuyến nghị</li>
                <li><strong>Luxstay</strong> - Prefix: <code>LS-</code> ✅ Khuyến nghị</li>
                <li><strong>Expedia</strong> - Prefix: <code>EX-</code> ⚠️ Có thể bị chặn bot</li>
                <li><strong>Hotels.com</strong> - Prefix: <code>HT-</code> ⚠️ Có thể bị chặn bot</li>
            </ul>
            <small class="text-muted mt-2 d-block">
                <strong>Lưu ý:</strong> Một số trang web như Expedia và Hotels.com có hệ thống bảo vệ chống bot rất mạnh, 
                có thể trả về lỗi 403. Nếu gặp lỗi này, vui lòng thử sử dụng các trang web khác được đánh dấu ✅.
            </small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.rooms.scrape') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="booking_url" class="form-label">URL trang đặt phòng *</label>
                <input type="url" 
                       name="booking_url" 
                       id="booking_url" 
                       class="form-control @error('booking_url') is-invalid @enderror" 
                       value="{{ old('booking_url') }}" 
                       placeholder="https://www.booking.com/... hoặc https://www.agoda.com/... hoặc https://www.expedia.com/..." 
                       required>
                @error('booking_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    <strong>Ví dụ URL hợp lệ:</strong><br>
                    • Booking.com: <code>https://www.booking.com/hotel/vn/m-danang.html</code><br>
                    • Agoda.com: <code>https://www.agoda.com/hotel-grand-plaza-hanoi/hotel/hanoi-vn.html</code><br>
                    • Expedia: <code>https://www.expedia.com/Hotel-Search?destination=Hanoi&startDate=2024-01-15&endDate=2024-01-16&adults=2</code><br>
                    • Traveloka: <code>https://www.traveloka.com/vi-vn/hotel/...</code><br>
                    • VnTravel: <code>https://www.vntravel.com/...</code><br>
                    • Mytour.vn: <code>https://www.mytour.vn/...</code><br>
                    • Luxstay: <code>https://www.luxstay.com/...</code><br>
                    <br>
                    <strong class="text-warning">⚠️ Lưu ý:</strong><br>
                    • <strong>Phải copy URL đầy đủ</strong> từ trình duyệt (bao gồm cả các tham số sau dấu <code>?</code>)<br>
                    • Không chỉ copy phần <code>?...</code> mà phải copy toàn bộ URL<br>
                    <br>
                    <strong class="text-danger">Không sử dụng:</strong><br>
                    • URL không đầy đủ: <code>https://www.expedia.com/Hotel-Search?...</code> ❌<br>
                    • URL trang thành phố: <code>https://www.agoda.com/city/ho-chi-minh-city-vn.html</code> ❌
                </small>
            </div>

            <div class="d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-download"></i> Import vào database
                </button>
                <button type="button" class="btn btn-success btn-lg" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export ra Excel
                </button>
            </div>
        </form>

        <div class="mt-4">
            <h5><i class="fas fa-lightbulb"></i> Lưu ý:</h5>
            <ul>
                <li>Hệ thống hỗ trợ <strong>8 trang web đặt phòng</strong>: Booking.com, Agoda.com, Expedia, Hotels.com, Traveloka, VnTravel, Mytour.vn, Luxstay</li>
                <li><strong>Quan trọng:</strong> Chỉ sử dụng URL trang chi tiết khách sạn hoặc trang tìm kiếm. Không sử dụng URL trang thành phố</li>
                <li>Quá trình import có thể mất vài phút tùy thuộc vào số lượng phòng</li>
                <li>Hệ thống sẽ tự động tải và lưu ảnh phòng</li>
                <li>Nếu không tìm thấy thông tin phòng, vui lòng kiểm tra lại URL và đảm bảo đó là trang chi tiết khách sạn</li>
                <li>Một số trang có thể yêu cầu JavaScript để hiển thị đầy đủ thông tin (có thể cần headless browser)</li>
                <li>Mỗi trang web có prefix riêng để phân biệt nguồn gốc phòng (BC-, AG-, EX-, HT-, TK-, VT-, MT-, LS-)</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportToExcel() {
    const url = document.getElementById('booking_url').value;
    if (!url) {
        alert('Vui lòng nhập URL trước!');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.rooms.scrape.export") }}';
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add URL
    const urlInput = document.createElement('input');
    urlInput.type = 'hidden';
    urlInput.name = 'booking_url';
    urlInput.value = url;
    form.appendChild(urlInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush
@endsection

