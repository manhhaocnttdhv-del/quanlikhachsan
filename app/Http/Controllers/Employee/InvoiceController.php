<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị hóa đơn để in
     */
    public function show($bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $booking = Booking::with(['user', 'room', 'payment', 'payments', 'additionalCharges'])->findOrFail($bookingId);

        // Chỉ cho phép in hóa đơn nếu đã thanh toán
        if (!$booking->payment || $booking->payment->payment_status !== 'completed') {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Chỉ có thể in hóa đơn khi booking đã thanh toán.');
        }

        return view('employee.invoices.show', compact('booking'));
    }

    /**
     * In hóa đơn (PDF hoặc HTML)
     */
    public function print($bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $booking = Booking::with(['user', 'room', 'payment', 'payments', 'additionalCharges'])->findOrFail($bookingId);

        if (!$booking->payment || $booking->payment->payment_status !== 'completed') {
            return redirect()->route('admin.employee.checkout.show', $booking->id)
                ->with('error', 'Chỉ có thể in hóa đơn khi booking đã thanh toán.');
        }

        return view('employee.invoices.print', compact('booking'));
    }
}

