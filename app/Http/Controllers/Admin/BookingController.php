<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Exports\BookingsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room', 'payment']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest()->paginate(15);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'room', 'payment'])->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

    public function create(Request $request)
    {
        $rooms = Room::available()->get();
        $users = User::all();
        
        // Lấy tham số từ URL nếu có (khi click từ phòng trống)
        $selectedRoomId = $request->get('room_id');
        $selectedCheckInDate = $request->get('check_in_date', date('Y-m-d'));
        $selectedCheckOutDate = $request->get('check_out_date', date('Y-m-d', strtotime('+1 day')));
        
        return view('admin.bookings.create', compact('rooms', 'users', 'selectedRoomId', 'selectedCheckInDate', 'selectedCheckOutDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'check_out_time' => 'nullable|date_format:H:i',
            'number_of_guests' => 'required|integer|min:1',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'special_requests' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,bank_transfer_qr',
            'payment_status' => 'nullable|in:pending,completed',
        ]);

        // Set default time nếu không có
        $validated['check_in_time'] = $validated['check_in_time'] ?? '14:00';
        $validated['check_out_time'] = $validated['check_out_time'] ?? '12:00';

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
        
        // Kết hợp date + time thành datetime
        // Đảm bảo format đúng: Y-m-d H:i
        // Convert về string Y-m-d nếu là Carbon instance
        $checkInDate = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOutDate = \Carbon\Carbon::parse($validated['check_out_date']);
        
        $checkIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
            $checkInDate->format('Y-m-d') . ' ' . $validated['check_in_time']);
        $checkOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
            $checkOutDate->format('Y-m-d') . ' ' . $validated['check_out_time']);
        
        // Kiểm tra nếu check-in và check-out cùng ngày (tính theo giờ)
        if ($checkIn->isSameDay($checkOut)) {
            // Tính theo giờ nếu cùng ngày
            $hours = $checkIn->diffInHours($checkOut);
            if ($hours < 1) {
                $hours = 1; // Tối thiểu 1 giờ
            }
            // Giá theo giờ = giá/đêm / 24
            $pricePerHour = $room->price_per_night / 24;
            $totalPrice = $hours * $pricePerHour;
            // Tối thiểu = 1/3 giá đêm (nếu thuê ít giờ)
            $minPrice = $room->price_per_night / 3;
            if ($totalPrice < $minPrice) {
                $totalPrice = $minPrice;
            }
            $validated['total_price'] = $totalPrice;
        } else {
            // Tính theo đêm nếu khác ngày
            // Tính số đêm dựa trên cả date và time
            // Số đêm = số ngày giữa check-in date và check-out date (tính cả 2 ngày)
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $checkOutDate = Carbon::parse($validated['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);
            
            // Nếu check-out time >= check-in time, tính thêm 1 đêm (đêm cuối)
            // Nếu check-out time < check-in time, không tính đêm cuối
            $checkInTimeStr = $validated['check_in_time'] ?? '14:00';
            $checkOutTimeStr = $validated['check_out_time'] ?? '12:00';
            
            if ($checkInTimeStr && $checkOutTimeStr && strpos($checkInTimeStr, ':') !== false && strpos($checkOutTimeStr, ':') !== false) {
                $checkInTimeParts = explode(':', $checkInTimeStr);
                $checkOutTimeParts = explode(':', $checkOutTimeStr);
                $checkInHour = (int)$checkInTimeParts[0];
                $checkInMinute = (int)$checkInTimeParts[1];
                $checkOutHour = (int)$checkOutTimeParts[0];
                $checkOutMinute = (int)$checkOutTimeParts[1];
                
                // Nếu check-out time >= check-in time, tính đêm cuối
                if ($checkOutHour > $checkInHour || ($checkOutHour == $checkInHour && $checkOutMinute >= $checkInMinute)) {
                    $nights += 1;
                }
            } else {
                // Mặc định tính đêm cuối
                $nights += 1;
            }
            
            // Nếu check-out time > 12:00, tính thêm 0.5 đêm (late check-out)
            if ($checkOutTimeStr && strpos($checkOutTimeStr, ':') !== false) {
                $timeParts = explode(':', $checkOutTimeStr);
                $hour = (int)$timeParts[0];
                if ($hour > 12) {
                    $nights += 0.5; // Trả phòng sau 12:00 tính thêm nửa đêm
                }
            }
            
            $validated['total_price'] = $nights * $room->price_per_night;
        }

        // Kiểm tra xem phòng đã được đặt trong khoảng thời gian này chưa
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
                        $overlappingBooking->check_in_date->format('d/m/Y') . 
                        ' đến ' . 
                        $overlappingBooking->check_out_date->format('d/m/Y') . 
                        '. Vui lòng chọn khoảng thời gian khác.',
                ]);
        }

        // Kiểm tra số người không vượt quá sức chứa phòng
        if ($validated['number_of_guests'] > $room->capacity) {
            return back()
                ->withInput()
                ->withErrors([
                    'number_of_guests' => 'Số người không được vượt quá sức chứa của phòng (' . $room->capacity . ' người).',
                ]);
        }

        $booking = Booking::create($validated);

        // Nếu có thông tin thanh toán, tạo payment
        if ($request->has('payment_method') && $request->payment_method && 
            $request->has('payment_status') && $request->payment_status) {
            
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'transaction_id' => 'ADMIN_' . $booking->id . '_' . time(),
                'payment_date' => $request->payment_status === 'completed' ? now() : null,
                'notes' => 'Thanh toán khi tạo đặt phòng từ admin',
            ]);

            // Nếu payment completed và booking status là pending, tự động chuyển sang confirmed
            if ($request->payment_status === 'completed' && $booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Tạo đặt phòng thành công!');
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $rooms = Room::all();
        $users = User::all();
        return view('admin.bookings.edit', compact('booking', 'rooms', 'users'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'special_requests' => 'nullable|string',
        ]);

        $booking->update($validated);

        // Cập nhật trạng thái phòng
        if ($validated['status'] === 'checked_in') {
            $booking->room->update(['status' => 'occupied']);
        } elseif ($validated['status'] === 'checked_out') {
            $booking->room->update(['status' => 'available']);
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Cập nhật đặt phòng thành công!');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Xóa đặt phòng thành công!');
    }

    public function export(Request $request)
    {
        $query = Booking::with(['user', 'room']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $filename = 'bookings_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new BookingsExport($query), $filename);
    }
}
