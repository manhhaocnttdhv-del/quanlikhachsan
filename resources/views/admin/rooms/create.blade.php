@extends('layouts.admin')

@section('title', 'Thêm phòng mới')

@push('styles')
<style>
    #imageUploadArea {
        transition: all 0.3s;
        cursor: pointer;
        border: 2px dashed #dee2e6;
    }
    #imageUploadArea:hover {
        border-color: #667eea;
        background-color: #f8f9ff;
    }
    #imageUploadArea.border-primary {
        border-color: #667eea !important;
        background-color: #f0f4ff;
    }
    #imagePreview img {
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle"></i> Thêm phòng mới</h2>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số phòng *</label>
                    <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" 
                           value="{{ old('room_number') }}" required>
                    @error('room_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại phòng *</label>
                    <select name="room_type" class="form-select @error('room_type') is-invalid @enderror" required>
                        <option value="">-- Chọn loại phòng --</option>
                        <option value="Standard" {{ old('room_type') == 'Standard' ? 'selected' : '' }}>Standard</option>
                        <option value="Deluxe" {{ old('room_type') == 'Deluxe' ? 'selected' : '' }}>Deluxe</option>
                        <option value="Suite" {{ old('room_type') == 'Suite' ? 'selected' : '' }}>Suite</option>
                        <option value="VIP" {{ old('room_type') == 'VIP' ? 'selected' : '' }}>VIP</option>
                    </select>
                    @error('room_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sức chứa (người) *</label>
                    <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                           value="{{ old('capacity', 2) }}" min="1" max="10" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Giá/đêm (VNĐ) *</label>
                    <input type="number" name="price_per_night" class="form-control @error('price_per_night') is-invalid @enderror" 
                           value="{{ old('price_per_night') }}" min="300000" step="1000" required>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Giá tối thiểu: 300,000 VNĐ/đêm
                    </small>
                    @error('price_per_night')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Trạng thái *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Trống</option>
                        <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Đã đặt</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tiện nghi</label>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="TV" id="amenity1">
                            <label class="form-check-label" for="amenity1">TV</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="WiFi" id="amenity2">
                            <label class="form-check-label" for="amenity2">WiFi</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Điều hòa" id="amenity3">
                            <label class="form-check-label" for="amenity3">Điều hòa</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Tủ lạnh" id="amenity4">
                            <label class="form-check-label" for="amenity4">Tủ lạnh</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Minibar" id="amenity5">
                            <label class="form-check-label" for="amenity5">Minibar</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Ban công" id="amenity6">
                            <label class="form-check-label" for="amenity6">Ban công</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Bồn tắm" id="amenity7">
                            <label class="form-check-label" for="amenity7">Bồn tắm</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="Phòng khách" id="amenity8">
                            <label class="form-check-label" for="amenity8">Phòng khách</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Hình ảnh chính</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Định dạng: JPG, PNG. Kích thước tối đa: 2MB</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Thêm nhiều ảnh</label>
                <div class="border rounded p-3 bg-light" id="imageUploadArea">
                    <input type="file" name="images[]" id="imagesInput" class="form-control @error('images.*') is-invalid @enderror" 
                           accept="image/*" multiple style="display: none;">
                    <div class="text-center py-4" id="uploadPlaceholder">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-2">Kéo thả ảnh vào đây hoặc click để chọn</p>
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imagesInput').click()">
                            <i class="fas fa-images me-2"></i>Chọn nhiều ảnh
                        </button>
                        <p class="text-muted small mt-2 mb-0">Định dạng: JPG, PNG. Kích thước tối đa: 2MB/ảnh</p>
                    </div>
                    <div id="imagePreview" class="row g-2 mt-3" style="display: none;"></div>
                </div>
                @error('images.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu phòng
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const imagesInput = document.getElementById('imagesInput');
    const imagePreview = document.getElementById('imagePreview');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const selectedFiles = [];

    // Click để chọn file
    imagesInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag & Drop
    const uploadArea = document.getElementById('imageUploadArea');
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('border-primary');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/') && file.size <= 2 * 1024 * 1024) {
                selectedFiles.push(file);
                previewImage(file);
            } else {
                alert('File ' + file.name + ' không hợp lệ hoặc quá lớn (max 2MB)');
            }
        });
        
        // Cập nhật input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        imagesInput.files = dataTransfer.files;
    }

    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3';
            col.innerHTML = `
                <div class="card position-relative">
                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    <div class="card-body p-2">
                        <p class="small text-muted mb-1">${file.name}</p>
                        <p class="small text-muted mb-0">${(file.size / 1024).toFixed(1)} KB</p>
                        <button type="button" class="btn btn-sm btn-danger w-100 mt-2" onclick="removeImage(this, '${file.name}')">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            `;
            col.dataset.fileName = file.name;
            imagePreview.appendChild(col);
            uploadPlaceholder.style.display = 'none';
            imagePreview.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function removeImage(btn, fileName) {
        // Xóa khỏi selectedFiles
        const index = selectedFiles.findIndex(f => f.name === fileName);
        if (index > -1) {
            selectedFiles.splice(index, 1);
        }
        
        // Cập nhật input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        imagesInput.files = dataTransfer.files;
        
        // Xóa preview
        btn.closest('.col-md-3').remove();
        
        // Hiển thị placeholder nếu không còn ảnh
        if (selectedFiles.length === 0) {
            uploadPlaceholder.style.display = 'block';
            imagePreview.style.display = 'none';
        }
    }
</script>
@endpush
@endsection

