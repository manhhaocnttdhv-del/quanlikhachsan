<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Tìm kiếm nhanh booking và khách hàng
     */
    public function search(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = $request->get('q', '');

        if (empty($query)) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vui lòng nhập từ khóa tìm kiếm.');
        }

        $results = [
            'bookings' => collect(),
            'customers' => collect(),
        ];

        // Tìm booking theo mã, tên khách, số phòng
        $bookings = Booking::with(['user', 'room'])
            ->where(function($q) use ($query) {
                $q->where('id', 'like', "%{$query}%")
                  ->orWhereHas('user', function($userQuery) use ($query) {
                      $userQuery->where('name', 'like', "%{$query}%")
                                ->orWhere('email', 'like', "%{$query}%")
                                ->orWhere('phone', 'like', "%{$query}%");
                  })
                  ->orWhereHas('room', function($roomQuery) use ($query) {
                      $roomQuery->where('room_number', 'like', "%{$query}%");
                  });
            })
            ->latest()
            ->take(10)
            ->get();

        // Tìm khách hàng
        $customers = User::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->take(10)
            ->get();

        return view('employee.quick-search.results', compact('query', 'bookings', 'customers'));
    }
}

