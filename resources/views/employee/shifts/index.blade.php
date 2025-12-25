@extends('layouts.admin')

@section('title', 'Ca làm việc của tôi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt"></i> Ca làm việc của tôi</h2>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employee.shifts.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Ngày</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Đã lên lịch</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang làm</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.employee.shifts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Ca làm việc</th>
                        <th>Giờ</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>{{ $shift->shift_date->format('d/m/Y') }}</td>
                            <td>
                                @if($shift->shift_type)
                                    <span class="badge bg-primary">{{ $shift->getShiftTypeName() }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                            <td>
                                @if($shift->status == 'scheduled')
                                    <span class="badge bg-info">Đã lên lịch</span>
                                @elseif($shift->status == 'active')
                                    <span class="badge bg-success">Đang làm</span>
                                @elseif($shift->status == 'completed')
                                    <span class="badge bg-secondary">Hoàn thành</span>
                                @else
                                    <span class="badge bg-danger">Đã hủy</span>
                                @endif
                            </td>
                            <td>
                                @if($shift->notes)
                                    <span title="{{ $shift->notes }}">{{ Str::limit($shift->notes, 30) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.employee.shifts.show', $shift->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($shift->status == 'scheduled')
                                        @php
                                            $shiftDate = \Carbon\Carbon::parse($shift->shift_date);
                                            $canDelete = $shiftDate->isFuture();
                                        @endphp
                                        <button class="btn btn-sm btn-success update-status-btn" 
                                                data-shift-id="{{ $shift->id }}" 
                                                data-status="active"
                                                data-status-name="Đang làm">
                                            <i class="fas fa-play"></i> Bắt đầu
                                        </button>
                                        @if($canDelete)
                                            <button class="btn btn-sm btn-danger delete-shift-btn" 
                                                    data-shift-id="{{ $shift->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @elseif($shift->status == 'active')
                                        <button class="btn btn-sm btn-warning update-status-btn" 
                                                data-shift-id="{{ $shift->id }}" 
                                                data-status="completed"
                                                data-status-name="Hoàn thành">
                                            <i class="fas fa-check"></i> Kết thúc
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Bạn chưa có ca làm việc nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $shifts->links() }}
        </div>
    </div>
</div>

<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.toast {
    min-width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>

<div class="toast-container"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateStatusButtons = document.querySelectorAll('.update-status-btn');
    
    // Hàm hiển thị thông báo
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
    
    // Hàm cập nhật UI sau khi thành công
    function updateUI(button, newStatus, statusName) {
        const row = button.closest('tr');
        const statusCell = row.querySelector('td:nth-child(4)');
        const actionCell = row.querySelector('td:nth-child(6)');
        
        // Cập nhật badge trạng thái
        let badgeClass = 'bg-info';
        let badgeText = 'Đã lên lịch';
        if (newStatus === 'active') {
            badgeClass = 'bg-success';
            badgeText = 'Đang làm';
        } else if (newStatus === 'completed') {
            badgeClass = 'bg-secondary';
            badgeText = 'Hoàn thành';
        }
        statusCell.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
        
        // Cập nhật nút hành động
        if (newStatus === 'active') {
            actionCell.innerHTML = `
                <button class="btn btn-sm btn-warning update-status-btn" 
                        data-shift-id="${button.getAttribute('data-shift-id')}" 
                        data-status="completed"
                        data-status-name="Hoàn thành">
                    <i class="fas fa-check"></i> Kết thúc ca
                </button>
            `;
            // Gắn lại event listener cho nút mới
            actionCell.querySelector('.update-status-btn').addEventListener('click', handleUpdateClick);
        } else if (newStatus === 'completed') {
            actionCell.innerHTML = '<span class="text-muted">-</span>';
        }
    }
    
    // Hàm xử lý click
    function handleUpdateClick() {
        const button = this;
        const shiftId = button.getAttribute('data-shift-id');
        const newStatus = button.getAttribute('data-status');
        const statusName = button.getAttribute('data-status-name');
        
        if (!confirm(`Bạn có chắc muốn cập nhật trạng thái ca sang "${statusName}"?`)) {
            return;
        }
        
        // Lưu HTML gốc
        const originalHTML = button.innerHTML;
        
        // Disable button
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
        const updateUrl = '{{ route("admin.employee.shifts.updateStatus", ":id") }}'.replace(':id', shiftId);
        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                updateUI(button, newStatus, statusName);
            } else {
                showToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'danger');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'danger');
            button.disabled = false;
            button.innerHTML = originalHTML;
        });
    }
    
    // Gắn event listener cho tất cả các nút
    updateStatusButtons.forEach(button => {
        button.addEventListener('click', handleUpdateClick);
    });
    
    // Xử lý xóa ca
    const deleteButtons = document.querySelectorAll('.delete-shift-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const shiftId = this.getAttribute('data-shift-id');
            
            if (!confirm('Bạn có chắc muốn xóa ca làm việc này?')) {
                return;
            }
            
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(`/admin/employee/shifts/${shiftId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Xóa dòng khỏi bảng
                    this.closest('tr').remove();
                } else {
                    showToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'danger');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'danger');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });
});
</script>
@endsection

