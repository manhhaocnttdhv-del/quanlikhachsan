<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị form yêu cầu hoàn tiền
     */
    public function create($paymentId)
    {
        $payment = Payment::with(['booking', 'booking.user'])->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra payment status
        if ($payment->payment_status !== 'completed') {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Chỉ có thể yêu cầu hoàn tiền cho thanh toán đã hoàn thành.');
        }

        // Kiểm tra booking status - cho phép yêu cầu hoàn tiền cho booking đã cancelled hoặc sắp hủy
        if (!in_array($payment->booking->status, ['pending', 'confirmed', 'cancelled'])) {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Không thể yêu cầu hoàn tiền cho booking này.');
        }
        
        // Nếu booking chưa cancelled, thông báo rằng sẽ hủy sau khi nhập thông tin hoàn tiền
        if ($payment->booking->status !== 'cancelled') {
            // Tính số tiền hoàn để hiển thị
            $checkInDate = \Carbon\Carbon::parse($payment->booking->check_in_date)->startOfDay();
            $today = \Carbon\Carbon::today()->startOfDay();
            $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
            $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
            $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
            
            $cancellationFee = 0;
            $refundAmount = $payment->amount;
            if ($daysUntilCheckIn < $cancellationDaysForFullRefund && $daysUntilCheckIn >= 1) {
                $cancellationFee = $payment->amount * ($cancellationFeePercentage / 100);
                $refundAmount = $payment->amount - $cancellationFee;
            }
        }

        // Kiểm tra xem đã có yêu cầu hoàn tiền chưa
        $existingRequest = RefundRequest::where('payment_id', $payment->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('info', 'Bạn đã có yêu cầu hoàn tiền đang được xử lý.');
        }

        return view('user.refunds.create', compact('payment'));
    }

    /**
     * Lưu yêu cầu hoàn tiền
     */
    public function store(Request $request, $paymentId)
    {
        $payment = Payment::with(['booking'])->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra payment status
        if ($payment->payment_status !== 'completed') {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Chỉ có thể yêu cầu hoàn tiền cho thanh toán đã hoàn thành.');
        }

        // Kiểm tra xem đã có yêu cầu hoàn tiền chưa
        $existingRequest = RefundRequest::where('payment_id', $payment->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Bạn đã có yêu cầu hoàn tiền đang được xử lý.');
        }

        $validated = $request->validate([
            'refund_method' => 'required|in:bank_transfer,qr_code',
            'bank_name' => 'required_if:refund_method,bank_transfer|nullable|string|max:255',
            'account_number' => 'required_if:refund_method,bank_transfer|nullable|string|max:50',
            'account_holder_name' => 'required_if:refund_method,bank_transfer|nullable|string|max:255',
            'qr_code_image' => 'required_if:refund_method,qr_code|nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
            'qr_code' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Tính số tiền hoàn (có thể tính phí hủy nếu booking chưa cancelled)
        $refundAmount = $payment->amount;
        $cancellationFee = 0;
        
        // Nếu booking chưa cancelled, tính phí hủy
        if ($payment->booking->status !== 'cancelled') {
            $checkInDate = \Carbon\Carbon::parse($payment->booking->check_in_date)->startOfDay();
            $today = \Carbon\Carbon::today()->startOfDay();
            $daysUntilCheckIn = $today->diffInDays($checkInDate, false);
            $cancellationDaysForFullRefund = config('apps.general.config.cancellation_days_for_full_refund', 1);
            $cancellationFeePercentage = config('apps.general.config.cancellation_fee_percentage', 10);
            
            // Nếu hủy trước đủ số ngày quy định: hoàn tiền đầy đủ
            if ($daysUntilCheckIn >= $cancellationDaysForFullRefund) {
                $refundAmount = $payment->amount;
            } else {
                // Nếu hủy trước 1 ngày nhưng không đủ số ngày quy định: tính phí hủy
                if ($daysUntilCheckIn >= 1) {
                    $cancellationFee = $payment->amount * ($cancellationFeePercentage / 100);
                    $refundAmount = $payment->amount - $cancellationFee;
                }
            }
        }

        DB::beginTransaction();
        try {
            // Xử lý upload ảnh QR Code nếu có
            $qrCodePath = null;
            if ($validated['refund_method'] === 'qr_code' && $request->hasFile('qr_code_image')) {
                $file = $request->file('qr_code_image');
                $fileName = 'refund_qr_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $qrCodePath = $file->storeAs('refunds/qr_codes', $fileName, 'public');
            }

            // Nếu có ảnh QR Code, lưu path. Nếu không, lưu text từ textarea
            $qrCodeValue = $qrCodePath ?? $validated['qr_code'] ?? null;

            RefundRequest::create([
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'user_id' => Auth::id(),
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'],
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder_name' => $validated['account_holder_name'] ?? null,
                'qr_code' => $qrCodeValue,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Nếu booking chưa cancelled, tự động hủy booking sau khi tạo yêu cầu hoàn tiền
            if ($payment->booking->status !== 'cancelled') {
                // Cập nhật payment status thành refunded
                $notes = ($payment->notes ? $payment->notes . "\n" : '') . 
                         "Đã hoàn tiền do hủy đặt phòng vào " . now()->format('d/m/Y H:i');
                if ($cancellationFee > 0) {
                    $notes .= "\nPhí hủy phòng: " . number_format($cancellationFee) . " VNĐ (" . $cancellationFeePercentage . "%)";
                    $notes .= "\nSố tiền hoàn: " . number_format($refundAmount) . " VNĐ";
                }
                
                $payment->update([
                    'payment_status' => 'refunded',
                    'notes' => $notes,
                ]);

                // Cập nhật trạng thái booking thành cancelled
                $payment->booking->update(['status' => 'cancelled']);

                // Cập nhật trạng thái phòng về available
                if ($payment->booking->room && $payment->booking->room->status === 'occupied') {
                    $payment->booking->room->update(['status' => 'available']);
                }
            }

            DB::commit();

            $message = 'Đã gửi yêu cầu hoàn tiền thành công! Đặt phòng đã được hủy. Admin sẽ xử lý hoàn tiền trong thời gian sớm nhất.';
            if ($cancellationFee > 0) {
                $message .= ' Phí hủy phòng: ' . number_format($cancellationFee) . ' VNĐ. Số tiền được hoàn: ' . number_format($refundAmount) . ' VNĐ.';
            } else {
                $message .= ' Số tiền được hoàn: ' . number_format($refundAmount) . ' VNĐ (100%).';
            }

            return redirect()->route('user.bookings.show', $payment->booking_id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Có lỗi xảy ra khi gửi yêu cầu hoàn tiền: ' . $e->getMessage());
        }
    }
}
