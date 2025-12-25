<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị danh sách khách hàng
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = User::query();

        // Tìm kiếm
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount('bookings')->latest()->paginate(15);

        return view('employee.customers.index', compact('customers'));
    }

    /**
     * Hiển thị chi tiết khách hàng và lịch sử booking
     */
    public function show($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $customer = User::with(['bookings' => function($query) {
            $query->with(['room', 'payment'])->latest();
        }])->findOrFail($id);

        return view('employee.customers.show', compact('customer'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin khách hàng
     */
    public function edit($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $customer = User::findOrFail($id);

        return view('employee.customers.edit', compact('customer'));
    }

    /**
     * Cập nhật thông tin khách hàng (chỉ SĐT và email)
     */
    public function update(Request $request, $id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $customer = User::findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.employee.customers.show', $customer->id)
            ->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }
}

