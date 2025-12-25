<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role.admin:admin');
    }

    public function index()
    {
        $staff = Admin::latest()->paginate(15);
        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,employee',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        Admin::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Thêm nhân viên thành công!');
    }

    public function show($id)
    {
        $staff = Admin::findOrFail($id);
        return view('admin.staff.show', compact('staff'));
    }

    public function edit($id)
    {
        $staff = Admin::findOrFail($id);
        return view('admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $staff = Admin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,employee',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $staff->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Cập nhật nhân viên thành công!');
    }

    public function destroy($id)
    {
        $staff = Admin::findOrFail($id);
        
        // Không cho phép xóa chính mình
        if (auth('admin')->id() === $staff->id) {
            return back()->with('error', 'Không thể xóa tài khoản của chính mình!');
        }

        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Xóa nhân viên thành công!');
    }
}
