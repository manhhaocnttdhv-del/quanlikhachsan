@extends('layouts.admin')

@section('title', 'Quản lý Nhân viên')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-tie"></i> Quản lý Nhân viên</h2>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm nhân viên
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td>#{{ $member->id }}</td>
                            <td><strong>{{ $member->name }}</strong></td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->phone ?? '-' }}</td>
                            <td>
                                @if($member->role == 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-info">Nhân viên</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at ? $member->created_at->format('d/m/Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.staff.show', $member->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth('admin')->id() !== $member->id)
                                    <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có nhân viên nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $staff->links() }}
        </div>
    </div>
</div>
@endsection

