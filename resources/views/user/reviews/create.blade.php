@extends('layouts.app')

@section('title', 'Đánh giá phòng ' . $room->room_number)

@push('styles')
<style>
    .rating-input {
        display: none;
    }
    .rating-label {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
        margin: 0;
        padding: 0;
        display: inline-block;
    }
    .rating-stars {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 0;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">Phòng</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.show', $room->id) }}">Phòng {{ $room->room_number }}</a></li>
            <li class="breadcrumb-item active">Đánh giá</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="fas fa-star text-warning"></i> Đánh giá phòng {{ $room->room_number }}
                    </h4>
                </div>
                <div class="card-body">
                    @if($booking)
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i> 
                            Bạn đang đánh giá cho đặt phòng #{{ $booking->id }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>{{ $room->room_number }} - {{ $room->room_type }}</h5>
                        <p class="text-muted mb-0">{{ $room->description }}</p>
                    </div>

                    <form action="{{ route('user.reviews.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        @if($booking)
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Đánh giá sao <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <div class="rating-stars">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="rating{{ $i }}" 
                                               class="rating-input" {{ old('rating') == $i ? 'checked' : '' }} required>
                                        <label for="rating{{ $i }}" class="rating-label me-1">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    @endfor
                                </div>
                                <span class="ms-3 text-muted" id="ratingText">Chọn số sao</span>
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="comment" class="form-label fw-bold">Nhận xét</label>
                            <textarea name="comment" id="comment" rows="5" class="form-control" 
                                      placeholder="Chia sẻ trải nghiệm của bạn về phòng này...">{{ old('comment') }}</textarea>
                            <small class="text-muted">Tối đa 1000 ký tự</small>
                            @error('comment')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi đánh giá
                            </button>
                            <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ratingInputs = document.querySelectorAll('.rating-input');
        const ratingText = document.getElementById('ratingText');
        const texts = {
            '1': 'Rất không hài lòng',
            '2': 'Không hài lòng',
            '3': 'Bình thường',
            '4': 'Hài lòng',
            '5': 'Rất hài lòng'
        };

        // Xử lý khi click vào sao
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const rating = parseInt(this.value);
                ratingText.textContent = texts[rating] || 'Chọn số sao';
                const labels = document.querySelectorAll('.rating-label');
                labels.forEach((label, index) => {
                    const starValue = 5 - index; // starValue: 5, 4, 3, 2, 1
                    if (starValue <= rating) {
                        label.style.color = '#ffc107';
                    } else {
                        label.style.color = '#ddd';
                    }
                });
            });
        });

        // Xử lý hover
        const labels = document.querySelectorAll('.rating-label');
        console.log(labels);
        
       for($i = labels.length - 1; $i >= 0; $i--) {
            labels[$i].addEventListener('mouseenter', function(e) {
                const currentIndex = Array.from(labels).indexOf(this);
                console.log(currentIndex);
                labels.forEach((l, i) => {
                    if (i >= currentIndex) {
                        l.style.color = '#ffc107';
                    } else {
                        l.style.color = '#ddd';
                    }
                });
            });
         }
        // labels.forEach((label, index) => {
        //     label.addEventListener('mouseenter', function() {
        //         const currentIndex = Array.from(labels).indexOf(this);
        //         // Highlight từ sao đầu tiên đến sao hiện tại
        //         console.log(currentIndex);
                
        //         labels.forEach((l, i) => {
        //             if (i <= currentIndex) {
        //                 l.style.color = '#ffc107';
        //             } else {
        //                 l.style.color = '#ddd';
        //             }
        //         });
        //     });
        // });

        // Reset khi mouse leave
        document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
            const checkedInput = document.querySelector('.rating-input:checked');
            if (checkedInput) {
                console.log(checkedInput);
                
                checkedInput.dispatchEvent(new Event('change'));
            } else {
                labels.forEach(label => {
                    label.style.color = '#ddd';
                });
                ratingText.textContent = 'Chọn số sao';
            }
        });

        // Set initial text if rating is already selected
        const checkedRating = document.querySelector('.rating-input:checked');
        if (checkedRating) {
            checkedRating.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush

