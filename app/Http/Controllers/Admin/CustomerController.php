<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('cccd', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount('bookings')->latest()->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = User::with(['bookings.room', 'bookings.payment'])->findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }

    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Xóa khách hàng thành công!');
    }

    public function create()
    {
        // Not implemented - customers register themselves
        abort(404);
    }

    public function store(Request $request)
    {
        // Not implemented - customers register themselves
        abort(404);
    }
}
