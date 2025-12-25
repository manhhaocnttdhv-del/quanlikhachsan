@extends('layouts.admin')

@section('title', 'Import phòng từ Excel/CSV')

@section('content')
{{-- <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-import"></i> Import phòng từ Excel/CSV</h2>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div> --}}

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Hướng dẫn:</strong> Tải file template Excel mẫu, điền thông tin phòng vào file, sau đó upload file để import vào hệ thống.
            <br><br>
            <strong>Lưu ý:</strong>
            <ul class="mb-0 mt-2">
                <li>File phải có định dạng Excel (.xlsx, .xls) hoặc CSV</li>
                <li>Kích thước file tối đa: 10MB</li>
                <li>File phải có header row (dòng đầu tiên chứa tên cột)</li>
                <li>Nếu số phòng đã tồn tại, hệ thống sẽ tự động tạo số phòng mới</li>
                <li>Trạng thái: <code>available</code> (Trống), <code>occupied</code> (Đã đặt), <code>maintenance</code> (Bảo trì)</li>
                <li>Loại phòng: <code>Standard</code>, <code>Deluxe</code>, <code>Suite</code>, <code>VIP</code></li>
                <li>Tiện nghi: Phân cách bằng dấu phẩy (ví dụ: WiFi, TV, Điều hòa)</li>
            </ul>
        </div>

        <div class="alert alert-success">
            <i class="fas fa-download"></i> 
            <strong>Tải file template mẫu:</strong>
            <a href="{{ route('admin.rooms.import.template') }}" class="btn btn-sm btn-success ms-2">
                <i class="fas fa-download"></i> Tải template Excel
            </a>
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

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.rooms.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label for="file" class="form-label">Chọn file Excel/CSV *</label>
                <input type="file" 
                       name="file" 
                       id="file" 
                       class="form-control @error('file') is-invalid @enderror" 
                       accept=".xlsx,.xls,.csv"
                       required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Định dạng: .xlsx, .xls, .csv | Kích thước tối đa: 10MB
                </small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-upload"></i> Bắt đầu import
                </button>
            </div>
        </form>

        <div class="mt-4">
            <h5><i class="fas fa-table"></i> Cấu trúc file Excel/CSV:</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Sức chứa</th>
                            <th>Giá/đêm (VNĐ)</th>
                            <th>Mô tả</th>
                            <th>Tiện nghi</th>
                            <th>Link hình ảnh</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>101</td>
                            <td>Standard</td>
                            <td>2</td>
                            <td>500000</td>
                            <td>Phòng tiêu chuẩn với view đẹp</td>
                            <td>WiFi, TV, Điều hòa</td>
                            <td>https://example.com/image1.jpg</td>
                            <td>available</td>
                        </tr>
                        <tr>
                            <td>102</td>
                            <td>Deluxe</td>
                            <td>3</td>
                            <td>800000</td>
                            <td>Phòng deluxe rộng rãi</td>
                            <td>WiFi, TV, Điều hòa, Minibar</td>
                            <td>https://example.com/image2.jpg</td>
                            <td>available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <h5><i class="fas fa-lightbulb"></i> Lưu ý:</h5>
            <ul>
                <li>File Excel/CSV phải có header row (dòng đầu tiên) chứa tên các cột</li>
                <li>Hệ thống hỗ trợ cả tên cột tiếng Việt và tiếng Anh</li>
                <li>Nếu không có giá trị, có thể để trống (trừ Số phòng)</li>
                <li>Tiện nghi có thể để trống hoặc phân cách bằng dấu phẩy</li>
                <li><strong>Link hình ảnh:</strong> Có thể là URL (https://...) hoặc đường dẫn file local. Hệ thống sẽ tự động tải và lưu hình ảnh</li>
                <li>Trạng thái mặc định là <code>available</code> nếu không điền</li>
                <li>Loại phòng mặc định là <code>Standard</code> nếu không điền</li>
            </ul>
        </div>
    </div>
</div>
@endsection

