<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Payment::with(['booking.user', 'booking.room']);

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest()->paginate(15);
        
        $totalRevenue = Payment::where('payment_status', 'completed')->sum('amount');
        $monthlyRevenue = Payment::where('payment_status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        return view('admin.payments.index', compact('payments', 'totalRevenue', 'monthlyRevenue'));
    }

    /**
     * Hiển thị form tạo thanh toán (Admin)
     */
    public function create(Request $request)
    {
        $bookingId = $request->get('booking_id');
        
        // Lấy danh sách bookings chưa thanh toán hoặc đã thanh toán nhưng chưa completed
        $bookings = Booking::with(['user', 'room', 'payment'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function($query) {
                $query->whereDoesntHave('payment')
                      ->orWhereHas('payment', function($q) {
                          $q->whereIn('payment_status', ['pending', 'failed']);
                      });
            })
            ->latest()
            ->get();

        // Nếu có booking_id, kiểm tra và pre-select
        $selectedBooking = null;
        if ($bookingId) {
            $selectedBooking = Booking::with(['user', 'room'])->find($bookingId);
            
            if ($selectedBooking) {
                // Kiểm tra xem đã có thanh toán completed chưa
                if ($selectedBooking->payment && $selectedBooking->payment->payment_status === 'completed') {
                    return redirect()->route('admin.bookings.show', $selectedBooking->id)
                        ->with('info', 'Đặt phòng này đã được thanh toán.');
                }
                
                // Thêm vào danh sách nếu chưa có
                if (!$bookings->contains('id', $selectedBooking->id)) {
                    $bookings->prepend($selectedBooking);
                }
            }
        }

        return view('admin.payments.create', compact('bookings', 'selectedBooking'));
    }

    /**
     * Lưu thanh toán mới (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:cash,bank_transfer_qr',
            'payment_status' => 'required|in:pending,completed',
            'amount' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::with('room')->findOrFail($validated['booking_id']);

        // Kiểm tra booking status
        if ($booking->status === 'cancelled') {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('error', 'Đặt phòng này đã bị hủy. Không thể tạo thanh toán.');
        }

        // Kiểm tra xem đã có payment completed chưa
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('error', 'Đặt phòng này đã có thanh toán hoàn tất.');
        }

        // Tạo payment
        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'transaction_id' => $validated['transaction_id'] ?? ('ADMIN_' . $booking->id . '_' . time()),
                'notes' => $validated['notes'] ?? null,
                'payment_date' => $validated['payment_status'] === 'completed' ? now() : null,
            ]
        );

        // Tự động cập nhật booking status nếu payment completed
        if ($validated['payment_status'] === 'completed' && $booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
        }

        return redirect()->route('admin.payments.show', $payment->id)
            ->with('success', 'Tạo thanh toán thành công!');
    }

    public function show($id)
    {
        $payment = Payment::with(['booking.user', 'booking.room'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }


    public function edit($id)
    {
        $payment = Payment::with('booking')->findOrFail($id);
        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $oldStatus = $payment->payment_status;

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Tự động cập nhật payment_date khi chuyển sang completed
        if ($validated['payment_status'] === 'completed' && !$payment->payment_date) {
            $validated['payment_date'] = now();
        }

        // Chỉ cập nhật notes nếu có thay đổi
        $updateData = [
            'payment_status' => $validated['payment_status'],
        ];
        
        if (isset($validated['payment_date'])) {
            $updateData['payment_date'] = $validated['payment_date'];
        }
        
        // Cập nhật notes nếu có
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $payment->update($updateData);

        // Tự động cập nhật trạng thái booking khi payment status thay đổi
        $booking = $payment->booking;
        
        if ($validated['payment_status'] === 'completed' && $oldStatus !== 'completed') {
            // Nếu thanh toán thành công, cập nhật booking status thành confirmed
            if ($booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }
        } elseif ($validated['payment_status'] === 'refunded') {
            // Nếu đã hoàn tiền, có thể cập nhật booking status thành cancelled
            if (in_array($booking->status, ['pending', 'confirmed'])) {
                $booking->update(['status' => 'cancelled']);
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Cập nhật thanh toán thành công!');
    }

    /**
     * Từ chối/Hủy thanh toán QR (Admin)
     */
    public function rejectPayment(Request $request, $id)
    {
        $payment = Payment::with('booking')->findOrFail($id);
        
        // Chỉ cho phép từ chối nếu payment method là bank_transfer_qr và status là pending
        if ($payment->payment_method !== 'bank_transfer_qr') {
            return redirect()->route('admin.payments.show', $payment->id)
                ->with('error', 'Chỉ có thể từ chối thanh toán QR chuyển khoản.');
        }

        // Không cho phép từ chối nếu đã completed hoặc refunded
        if (in_array($payment->payment_status, ['completed', 'refunded'])) {
            return redirect()->route('admin.payments.show', $payment->id)
                ->with('error', 'Không thể từ chối thanh toán đã hoàn thành hoặc đã hoàn tiền.');
        }

        $validated = $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        // Cập nhật payment status thành failed
        $updateData = [
            'payment_status' => 'failed',
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 
                      "[ADMIN] Đã từ chối thanh toán QR vào " . now()->format('d/m/Y H:i') . 
                      ". Lý do: " . $validated['reject_reason'],
        ];

        $payment->update($updateData);

        // Cập nhật booking status thành cancelled và room status thành available
        $booking = $payment->booking;
        if (in_array($booking->status, ['pending'])) {
            $booking->update(['status' => 'cancelled']);
            $booking->room->update(['status' => 'available']);
        }

        return redirect()->route('admin.payments.show', $payment->id)
            ->with('success', 'Đã từ chối thanh toán QR thành công. Đặt phòng đã được hủy.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Xóa thanh toán thành công!');
    }

}
