<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Danh sách đặt phòng
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = Booking::with(['user', 'room', 'payment', 'payments']);

        // Filter theo status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter theo ngày check-in
        if ($request->has('check_in_date') && $request->check_in_date != '') {
            $query->where('check_in_date', $request->check_in_date);
        }

        // Filter theo ngày check-out
        if ($request->has('check_out_date') && $request->check_out_date != '') {
            $query->where('check_out_date', $request->check_out_date);
        }

        // Tìm kiếm
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('room', function($roomQuery) use ($search) {
                    $roomQuery->where('room_number', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate(20)->withQueryString();

        // Thống kê
        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'checked_in' => Booking::where('status', 'checked_in')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        return view('employee.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Hiển thị form đặt phòng cho nhân viên
     */
    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = \App\Models\Shift::where('shift_date', \Carbon\Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->first();

        if ($otherActiveShift) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Nhân viên khác đang làm việc. Bạn không thể tạo booking lúc này. Vui lòng đợi nhân viên hiện tại kết thúc ca.');
        }

        $rooms = Room::where('status', 'available')->get();
        $users = User::all();
        
        // Lấy tham số từ URL nếu có (khi click từ phòng trống)
        $selectedRoomId = $request->get('room_id');
        $selectedCheckInDate = $request->get('check_in_date', date('Y-m-d'));
        $selectedCheckOutDate = $request->get('check_out_date', date('Y-m-d', strtotime('+1 day')));
        
        return view('employee.bookings.create', compact('rooms', 'users', 'selectedRoomId', 'selectedCheckInDate', 'selectedCheckOutDate'));
    }

    /**
     * Lưu đặt phòng mới
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = \App\Models\Shift::where('shift_date', \Carbon\Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->first();

        if ($otherActiveShift) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Nhân viên khác đang làm việc. Bạn không thể tạo booking lúc này. Vui lòng đợi nhân viên hiện tại kết thúc ca.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'check_out_time' => 'nullable|date_format:H:i',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

        // Set default time nếu không có
        $validated['check_in_time'] = $validated['check_in_time'] ?? '14:00';
        $validated['check_out_time'] = $validated['check_out_time'] ?? '12:00';
        $validated['status'] = 'confirmed'; // Tự động xác nhận khi nhân viên đặt

        // Kiểm tra nếu cùng ngày thì check-out time phải sau check-in time
        if ($validated['check_in_date'] === $validated['check_out_date']) {
            if ($validated['check_out_time'] <= $validated['check_in_time']) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'check_out_time' => 'Giờ trả phòng phải sau giờ nhận phòng khi cùng ngày.',
                    ]);
            }
        }

        $room = Room::findOrFail($validated['room_id']);
        
        // Tính giá tương tự như admin booking
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        
        $checkIn = Carbon::createFromFormat('Y-m-d H:i', 
            $checkInDate->format('Y-m-d') . ' ' . $validated['check_in_time']);
        $checkOut = Carbon::createFromFormat('Y-m-d H:i', 
            $checkOutDate->format('Y-m-d') . ' ' . $validated['check_out_time']);
        
        // Tính giá
        if ($checkIn->isSameDay($checkOut)) {
            $hours = $checkIn->diffInHours($checkOut);
            if ($hours < 1) {
                $hours = 1;
            }
            $pricePerHour = $room->price_per_night / 24;
            $totalPrice = $hours * $pricePerHour;
            $minPrice = $room->price_per_night / 3;
            if ($totalPrice < $minPrice) {
                $totalPrice = $minPrice;
            }
            $validated['total_price'] = $totalPrice;
        } else {
            $nights = $checkInDate->diffInDays($checkOutDate);
            $checkInTimeStr = $validated['check_in_time'] ?? '14:00';
            $checkOutTimeStr = $validated['check_out_time'] ?? '12:00';
            
            if ($checkInTimeStr && $checkOutTimeStr && strpos($checkInTimeStr, ':') !== false && strpos($checkOutTimeStr, ':') !== false) {
                $checkInTimeParts = explode(':', $checkInTimeStr);
                $checkOutTimeParts = explode(':', $checkOutTimeStr);
                $checkInHour = (int)$checkInTimeParts[0];
                $checkInMinute = (int)$checkInTimeParts[1];
                $checkOutHour = (int)$checkOutTimeParts[0];
                $checkOutMinute = (int)$checkOutTimeParts[1];
                
                if ($checkOutHour > $checkInHour || ($checkOutHour == $checkInHour && $checkOutMinute >= $checkInMinute)) {
                    $nights += 1;
                }
            } else {
                $nights += 1;
            }
            
            if ($checkOutTimeStr && strpos($checkOutTimeStr, ':') !== false) {
                $timeParts = explode(':', $checkOutTimeStr);
                $hour = (int)$timeParts[0];
                if ($hour > 12) {
                    $nights += 0.5;
                }
            }
            
            $validated['total_price'] = $nights * $room->price_per_night;
        }

        // Kiểm tra phòng trùng lịch
        $overlappingBooking = Booking::overlapping(
            $validated['room_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        )->first();

        if ($overlappingBooking) {
            return back()
                ->withInput()
                ->withErrors([
                    'check_in_date' => 'Phòng này đã được đặt từ ' . 
                        Carbon::parse($overlappingBooking->check_in_date)->format('d/m/Y') . 
                        ' đến ' . 
                        Carbon::parse($overlappingBooking->check_out_date)->format('d/m/Y') . 
                        '. Vui lòng chọn khoảng thời gian khác.',
                ]);
        }

        // Kiểm tra số người
        if ($validated['number_of_guests'] > $room->capacity) {
            return back()
                ->withInput()
                ->withErrors([
                    'number_of_guests' => 'Số người không được vượt quá sức chứa của phòng (' . $room->capacity . ' người).',
                ]);
        }

        Booking::create($validated);

        return redirect()->route('admin.employee.checkout.index')
            ->with('success', 'Đặt phòng thành công!');
    }

    /**
     * API: Lấy danh sách phòng trống theo khoảng thời gian
     */
    public function getAvailableRooms(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
        ]);

        $checkInDate = $request->check_in_date;
        $checkOutDate = $request->check_out_date;

        // Lấy tất cả phòng available
        $allRooms = Room::where('status', 'available')->get();

        // Lọc các phòng trống trong khoảng thời gian
        $availableRooms = $allRooms->filter(function($room) use ($checkInDate, $checkOutDate) {
            // Kiểm tra xem có booking nào trùng lịch không
            $overlapping = Booking::overlapping(
                $room->id,
                $checkInDate,
                $checkOutDate
            )->exists();

            return !$overlapping;
        });

        // Format dữ liệu để trả về
        $rooms = $availableRooms->map(function($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'price_per_night' => $room->price_per_night,
                'capacity' => $room->capacity,
                'display_text' => $room->room_number . ' - ' . $room->room_type . ' (' . number_format($room->price_per_night) . ' VNĐ/đêm)',
            ];
        });

        return response()->json([
            'rooms' => $rooms->values(),
            'count' => $rooms->count(),
        ]);
    }

    /**
     * Tạo khách hàng mới
     */
    public function createCustomer(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
        ]);

        // Tạo password mặc định (khách hàng có thể đổi sau)
        $password = \Illuminate\Support\Str::random(8);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'cccd' => $validated['cccd'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo khách hàng thành công!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }
}

