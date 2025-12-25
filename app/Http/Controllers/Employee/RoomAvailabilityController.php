<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị danh sách phòng trống hôm nay
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $today = Carbon::today();
        $date = $request->get('date', $today->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Lấy tất cả phòng available
        $allRooms = Room::where('status', 'available')
            ->with('primaryImage')
            ->orderBy('room_number')
            ->get();

        // Lọc phòng trống trong ngày đã chọn
        $availableRooms = $allRooms->filter(function($room) use ($selectedDate) {
            // Kiểm tra xem có booking nào trong ngày này không
            $hasBooking = Booking::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('check_in_date', '<=', $selectedDate)
                ->where('check_out_date', '>', $selectedDate)
                ->exists();

            return !$hasBooking;
        });

        // Lấy phòng đã đặt với thông tin booking
        $occupiedRooms = $allRooms->filter(function($room) use ($selectedDate) {
            return Booking::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('check_in_date', '<=', $selectedDate)
                ->where('check_out_date', '>', $selectedDate)
                ->exists();
        })->map(function($room) use ($selectedDate) {
            $room->current_booking = Booking::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('check_in_date', '<=', $selectedDate)
                ->where('check_out_date', '>', $selectedDate)
                ->with('user')
                ->first();
            return $room;
        });

        // Lấy phòng bảo trì
        $maintenanceRooms = Room::where('status', 'maintenance')
            ->with('primaryImage')
            ->orderBy('room_number')
            ->get();

        return view('employee.rooms.availability', compact(
            'availableRooms',
            'occupiedRooms',
            'maintenanceRooms',
            'selectedDate',
            'date'
        ));
    }

    /**
     * Tìm phòng trống theo khoảng thời gian
     */
    public function search(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'room_type' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);

        // Lấy tất cả phòng available
        $query = Room::where('status', 'available');

        if (isset($validated['room_type']) && $validated['room_type'] != '') {
            $query->where('room_type', $validated['room_type']);
        }

        if (isset($validated['capacity']) && $validated['capacity'] > 0) {
            $query->where('capacity', '>=', $validated['capacity']);
        }

        $allRooms = $query->with('primaryImage')->get();

        // Lọc các phòng trống trong khoảng thời gian
        $availableRooms = $allRooms->filter(function($room) use ($checkInDate, $checkOutDate) {
            $overlapping = Booking::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where(function($q) use ($checkInDate, $checkOutDate) {
                    $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                      ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                      ->orWhere(function($query) use ($checkInDate, $checkOutDate) {
                          $query->where('check_in_date', '<=', $checkInDate)
                                ->where('check_out_date', '>=', $checkOutDate);
                      });
                })
                ->exists();

            return !$overlapping;
        });

        return view('employee.rooms.search', compact(
            'availableRooms',
            'checkInDate',
            'checkOutDate',
            'validated'
        ));
    }

    /**
     * Xem lịch phòng (calendar view)
     */
    public function calendar(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $roomId = $request->get('room_id');
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Lấy tất cả phòng
        $rooms = Room::orderBy('room_number')->get();

        // Lấy booking trong tháng
        $bookingsQuery = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->orWhereBetween('check_out_date', [$startDate, $endDate])
            ->orWhere(function($q) use ($startDate, $endDate) {
                $q->where('check_in_date', '<=', $startDate)
                  ->where('check_out_date', '>=', $endDate);
            });

        if ($roomId) {
            $bookingsQuery->where('room_id', $roomId);
        }

        $bookings = $bookingsQuery->with(['room', 'user'])->get();

        // Tạo calendar data
        $calendarData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $calendarData[$dateStr] = [
                'date' => $currentDate->copy(),
                'rooms' => []
            ];

            foreach ($rooms as $room) {
                $isOccupied = $bookings->filter(function($booking) use ($room, $currentDate) {
                    return $booking->room_id == $room->id &&
                           $currentDate >= Carbon::parse($booking->check_in_date) &&
                           $currentDate < Carbon::parse($booking->check_out_date);
                })->count() > 0;

                $calendarData[$dateStr]['rooms'][$room->id] = [
                    'room' => $room,
                    'is_occupied' => $isOccupied,
                    'bookings' => $bookings->filter(function($booking) use ($room, $currentDate) {
                        return $booking->room_id == $room->id &&
                               $currentDate >= Carbon::parse($booking->check_in_date) &&
                               $currentDate < Carbon::parse($booking->check_out_date);
                    })
                ];
            }

            $currentDate->addDay();
        }

        return view('employee.rooms.calendar', compact(
            'calendarData',
            'rooms',
            'month',
            'year',
            'roomId',
            'startDate',
            'endDate'
        ));
    }
}

