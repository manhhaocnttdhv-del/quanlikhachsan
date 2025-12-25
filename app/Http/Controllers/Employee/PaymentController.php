<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị form tạo/cập nhật thanh toán
     */
    public function create($bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = \App\Models\Shift::where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->first();

        if ($otherActiveShift) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Nhân viên khác đang làm việc. Bạn không thể thực hiện thao tác này lúc này.');
        }

        $booking = Booking::with(['user', 'room', 'payment'])->findOrFail($bookingId);

        // Kiểm tra booking có status hợp lệ
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Booking này không ở trạng thái hợp lệ để thanh toán.');
        }

        // Kiểm tra xem đã có payment completed chưa
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('info', 'Đặt phòng này đã được thanh toán.');
        }

        return view('employee.payments.create', compact('booking'));
    }

    /**
     * Lưu thanh toán mới hoặc cập nhật thanh toán
     */
    public function store(Request $request, $bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = \App\Models\Shift::where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->first();

        if ($otherActiveShift) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Nhân viên khác đang làm việc. Bạn không thể thực hiện thao tác này lúc này.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer_qr',
            'payment_status' => 'required|in:pending,completed',
            'amount' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::with('room')->findOrFail($bookingId);

        // Kiểm tra booking status
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Booking này không ở trạng thái hợp lệ để thanh toán.');
        }

        // Kiểm tra xem đã có payment completed chưa
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Đặt phòng này đã có thanh toán hoàn tất.');
        }

        // Lấy ca làm việc hiện tại của nhân viên
        $currentShift = $admin->shifts()
            ->where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->first();

        // Tạo hoặc cập nhật payment
        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'transaction_id' => $validated['transaction_id'] ?? ('EMP_' . $booking->id . '_' . time()),
                'notes' => ($validated['notes'] ?? '') . 
                          ($currentShift ? ' [Nhân viên: ' . $admin->name . ' - Ca: ' . $currentShift->shift_type . ']' : ''),
                'payment_date' => $validated['payment_status'] === 'completed' ? now() : null,
            ]
        );

        // Tự động cập nhật booking status nếu payment completed
        if ($validated['payment_status'] === 'completed' && $booking->status === 'confirmed') {
            // Không cần thay đổi status vì đã là confirmed
        }

        // Gán shift_id cho booking nếu có
        if ($currentShift && !$booking->shift_id) {
            $booking->update(['shift_id' => $currentShift->id]);
        }

        return redirect()->route('admin.employee.checkout.show', $booking->id)
            ->with('success', 'Thanh toán đã được lưu thành công!');
    }

    /**
     * Cập nhật trạng thái thanh toán (chuyển từ pending sang completed)
     */
    public function update(Request $request, $bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = \App\Models\Shift::where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->first();

        if ($otherActiveShift) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Nhân viên khác đang làm việc. Bạn không thể thực hiện thao tác này lúc này.');
        }

        $booking = Booking::with('payment')->findOrFail($bookingId);

        if (!$booking->payment) {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Booking này chưa có thanh toán.');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:completed',
        ]);

        // Kiểm tra payment status
        if ($booking->payment->payment_status === 'completed') {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('info', 'Thanh toán này đã được xác nhận rồi.');
        }

        // Lấy ca làm việc hiện tại của nhân viên
        $currentShift = $admin->shifts()
            ->where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->first();

        // Cập nhật payment status
        $notes = $booking->payment->notes ?? '';
        if ($currentShift) {
            $notes .= ($notes ? "\n" : '') . "[NHÂN VIÊN] Đã xác nhận thanh toán bởi " . $admin->name . " - Ca: " . $currentShift->shift_type . " vào " . now()->format('d/m/Y H:i');
        } else {
            $notes .= ($notes ? "\n" : '') . "[NHÂN VIÊN] Đã xác nhận thanh toán bởi " . $admin->name . " vào " . now()->format('d/m/Y H:i');
        }

        $booking->payment->update([
            'payment_status' => 'completed',
            'payment_date' => now(),
            'notes' => $notes,
        ]);

        // Tự động cập nhật booking status nếu đang pending
        if ($booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
        }

        // Gán shift_id cho booking nếu có
        if ($currentShift && !$booking->shift_id) {
            $booking->update(['shift_id' => $currentShift->id]);
        }

        return redirect()->route('admin.employee.checkout.show', $booking->id)
            ->with('success', 'Đã xác nhận thanh toán thành công! Booking đã được cập nhật.');
    }
}

