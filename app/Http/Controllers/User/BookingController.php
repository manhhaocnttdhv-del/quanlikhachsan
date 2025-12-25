<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Tự động cập nhật status thành checked_out nếu quá ngày check_out
        $today = Carbon::today();
        Auth::user()->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_out_date', '<', $today)
            ->update(['status' => 'checked_out']);

        // Cập nhật room status về available nếu booking đã checked_out
        $checkedOutBookings = Auth::user()->bookings()
            ->where('status', 'checked_out')
            ->where('check_out_date', '<', $today)
            ->with('room')
            ->get();
        
        foreach ($checkedOutBookings as $booking) {
            if ($booking->room && $booking->room->status === 'occupied') {
                $booking->room->update(['status' => 'available']);
            }
        }

        $bookings = Auth::user()->bookings()->with(['room', 'room.images', 'payment', 'review'])->latest()->paginate(10);
        return view('user.bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $room = Room::findOrFail($request->room_id);
        return view('user.bookings.create', compact('room'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'check_out_time' => 'required|date_format:H:i',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

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
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        
        $checkIn = Carbon::createFromFormat('Y-m-d H:i', 
            $checkInDate->format('Y-m-d') . ' ' . $validated['check_in_time']);
        $checkOut = Carbon::createFromFormat('Y-m-d H:i', 
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
        } else {
            // Tính theo đêm nếu khác ngày
            // Tính số đêm dựa trên cả date và time
            // Số đêm = số ngày giữa check-in date và check-out date (tính cả 2 ngày)
            // Ví dụ: 31/12/2025 → 03/01/2026 = 3 đêm
            $nights = $checkInDate->diffInDays($checkOutDate);
            
            // Nếu check-out time >= check-in time, tính thêm 1 đêm (đêm cuối)
            // Nếu check-out time < check-in time, không tính đêm cuối
            $checkInTimeStr = $validated['check_in_time'];
            $checkOutTimeStr = $validated['check_out_time'];
            
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
            
            $totalPrice = $nights * $room->price_per_night;
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
                        Carbon::parse($overlappingBooking->check_in_date)->format('d/m/Y') . 
                        ' đến ' . 
                        Carbon::parse($overlappingBooking->check_out_date)->format('d/m/Y') . 
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

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'check_in_date' => $validated['check_in_date'],
            'check_in_time' => $validated['check_in_time'],
            'check_out_date' => $validated['check_out_date'],
            'check_out_time' => $validated['check_out_time'],
            'number_of_guests' => $validated['number_of_guests'],
            'total_price' => $totalPrice,
            'special_requests' => $validated['special_requests'],
            'status' => 'pending',
        ]);

        return redirect()->route('user.bookings.show', $booking->id)
            ->with('success', 'Đặt phòng thành công! Vui lòng thanh toán để xác nhận.');
    }

    public function show($id)
    {
        $booking = Booking::with(['room', 'payment', 'review'])->findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Tự động cập nhật status thành checked_out nếu quá ngày check_out
        $today = Carbon::today();
        if (in_array($booking->status, ['confirmed', 'checked_in']) && $booking->check_out_date < $today) {
            $booking->update(['status' => 'checked_out']);
            if ($booking->room && $booking->room->status === 'occupied') {
                $booking->room->update(['status' => 'available']);
            }
            // Reload booking để có status mới
            $booking->refresh();
        }

        return view('user.bookings.show', compact('booking'));
    }

    public function cancel($id)
    {
        $booking = Booking::with('payment')->findOrFail($id);
        
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra trạng thái booking có thể hủy
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Không thể hủy đặt phòng này.');
        }

        // Kiểm tra thời gian hủy
        $checkInDate = Carbon::parse($booking->check_in_date)->startOfDay();
        $today = Carbon::today()->startOfDay();
        $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
        
        $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
        
        // Không cho phép hủy nếu đã quá gần ngày check-in (ít hơn 1 ngày)
        if ($daysUntilCheckIn < 1) {
            return back()->with('error', 'Chỉ có thể hủy đặt phòng trước 1 ngày so với ngày nhận phòng.');
        }

        // Nếu đã thanh toán, yêu cầu nhập thông tin hoàn tiền trước khi hủy
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            // Kiểm tra xem đã có yêu cầu hoàn tiền chưa
            $hasRefundRequest = \App\Models\RefundRequest::where('payment_id', $booking->payment->id)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->exists();

            if (!$hasRefundRequest) {
                // Chưa có yêu cầu hoàn tiền, redirect đến form nhập thông tin
                return redirect()->route('user.refunds.create', $booking->payment->id)
                    ->with('info', 'Vui lòng nhập thông tin tài khoản/QR Code để nhận hoàn tiền trước khi hủy đặt phòng.');
            }
        }

        // Tính phí hủy phòng nếu hủy trước 1 ngày (nhưng không đủ số ngày để được hoàn tiền đầy đủ)
        $cancellationFee = 0;
        $refundAmount = 0;
        $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
        
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            $totalPaid = $booking->payment->amount;
            
            // Nếu hủy trước đủ số ngày quy định: hoàn tiền đầy đủ
            if ($daysUntilCheckIn >= $cancellationDaysForFullRefund) {
                $refundAmount = $totalPaid;
                $refundMessage = ' Bạn sẽ được hoàn tiền đầy đủ (' . number_format($refundAmount) . ' VNĐ).';
            } else {
                // Nếu hủy trước 1 ngày nhưng không đủ số ngày quy định: tính phí hủy
                $cancellationFee = $totalPaid * ($cancellationFeePercentage / 100);
                $refundAmount = $totalPaid - $cancellationFee;
                $refundMessage = ' Phí hủy phòng: ' . number_format($cancellationFee) . ' VNĐ (' . $cancellationFeePercentage . '% tổng tiền).';
                $refundMessage .= ' Số tiền được hoàn: ' . number_format($refundAmount) . ' VNĐ.';
            }

            // Cập nhật payment status thành refunded
            $notes = ($booking->payment->notes ? $booking->payment->notes . "\n" : '') . 
                     "Đã hoàn tiền do hủy đặt phòng vào " . now()->format('d/m/Y H:i');
            if ($cancellationFee > 0) {
                $notes .= "\nPhí hủy phòng: " . number_format($cancellationFee) . " VNĐ (" . $cancellationFeePercentage . "%)";
                $notes .= "\nSố tiền hoàn: " . number_format($refundAmount) . " VNĐ";
            }
            
            $booking->payment->update([
                'payment_status' => 'refunded',
                'notes' => $notes,
            ]);

            // Xác định phương thức thanh toán để thông báo
            $paymentMethod = $booking->payment->payment_method;
            if ($paymentMethod === 'vnpay') {
                $refundMessage .= ' Tiền sẽ được hoàn về tài khoản VNPay trong vòng 3-5 ngày làm việc.';
            } elseif ($paymentMethod === 'cash') {
                $refundMessage .= ' Vui lòng liên hệ khách sạn để nhận hoàn tiền.';
            } else {
                $refundMessage .= ' Tiền sẽ được hoàn về tài khoản trong vòng 3-5 ngày làm việc.';
            }
        } else {
            $refundMessage = '';
        }

        // Cập nhật trạng thái booking
        $booking->update(['status' => 'cancelled']);

        // Cập nhật trạng thái phòng về available
        $booking->room->update(['status' => 'available']);

        return back()->with('success', 'Đã hủy đặt phòng thành công.' . $refundMessage);
    }
}
