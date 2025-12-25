<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($bookingId)
    {
        $booking = Booking::with('room')->findOrFail($bookingId);
        
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra xem đã có thanh toán chưa
        if ($booking->payment && $booking->payment->payment_status === 'completed') {
            return redirect()->route('user.bookings.show', $booking->id)
                ->with('info', 'Đặt phòng này đã được thanh toán.');
        }

        // Không cho phép thanh toán nếu booking đã bị hủy
        if ($booking->status === 'cancelled') {
            return redirect()->route('user.bookings.show', $booking->id)
                ->with('error', 'Đặt phòng này đã bị hủy. Không thể thanh toán.');
        }

        // Không cho phép thanh toán lại nếu payment đã bị admin từ chối
        // Kiểm tra cả payment status failed và có prefix [ADMIN] trong notes
        if ($booking->payment) {
            if ($booking->payment->payment_status === 'failed') {
                $notes = $booking->payment->notes ?? '';
                if (str_contains($notes, '[ADMIN]')) {
                    return redirect()->route('user.bookings.show', $booking->id)
                        ->with('error', 'Thanh toán đã bị admin từ chối. Không thể thanh toán lại.');
                }
            }
        }

        // Cho phép tạo payment mới nếu payment cũ đã failed (do user hủy) hoặc refunded
        // (payment status pending vẫn có thể tạo payment mới để đổi phương thức)

        return view('user.payments.create', compact('booking'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:cash,bank_transfer_qr',
        ]);

        $booking = Booking::with('room')->findOrFail($validated['booking_id']);
        
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra lại booking status (bảo vệ kép)
        if ($booking->status === 'cancelled') {
            return redirect()->route('user.bookings.show', $booking->id)
                ->with('error', 'Đặt phòng này đã bị hủy. Không thể thanh toán.');
        }

        // Kiểm tra lại payment đã bị admin từ chối (bảo vệ kép)
        if ($booking->payment && $booking->payment->payment_status === 'failed') {
            $notes = $booking->payment->notes ?? '';
            if (str_contains($notes, '[ADMIN]')) {
                return redirect()->route('user.bookings.show', $booking->id)
                    ->with('error', 'Thanh toán đã bị admin từ chối. Không thể thanh toán lại.');
            }
        }

        // Xử lý thanh toán tiền mặt trực tiếp tại khách sạn
        if ($validated['payment_method'] === 'cash') {
            // Tạo payment với status pending (chờ thanh toán tại khách sạn)
            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $booking->total_price,
                    'payment_method' => 'cash',
                    'payment_status' => 'pending',
                    'payment_date' => null, // Chưa thanh toán nên chưa có ngày
                    'transaction_id' => 'CASH_' . $booking->id . '_' . time(),
                    'notes' => 'Thanh toán tiền mặt trực tiếp tại khách sạn - Chờ thanh toán',
                ]
            );

            return redirect()->route('user.bookings.show', $booking->id)
                ->with('success', 'Đã chọn thanh toán tiền mặt trực tiếp tại khách sạn. Vui lòng thanh toán khi đến nhận phòng. Admin sẽ xác nhận sau khi bạn thanh toán.');
        }

        // Xử lý QR Chuyển khoản
        if ($validated['payment_method'] === 'bank_transfer_qr') {
            // Tạo payment với status pending
            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $booking->total_price,
                    'payment_method' => 'bank_transfer_qr',
                    'payment_status' => 'pending',
                    'transaction_id' => 'QR_' . $booking->id . '_' . time(),
                ]
            );

            // Chuyển hướng đến trang hiển thị QR code
            return redirect()->route('user.payments.qr', $payment->id);
        }
    }

    /**
     * Hiển thị chi tiết thanh toán
     */
    public function show($paymentId)
    {
        $payment = Payment::with(['booking.user', 'booking.room'])->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Không cho phép xem chi tiết thanh toán nếu booking đã bị hủy
        if ($payment->booking->status === 'cancelled') {
            return redirect()->route('user.bookings.show', $payment->booking_id)
                ->with('error', 'Không thể xem chi tiết thanh toán của đặt phòng đã bị hủy.');
        }

        return view('user.payments.show', compact('payment'));
    }

    /**
     * Hiển thị QR code chuyển khoản
     */
    public function showQR($paymentId)
    {
        $payment = Payment::with(['booking.user', 'booking.room'])->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra phương thức thanh toán
        if ($payment->payment_method !== 'bank_transfer_qr') {
            return redirect()->route('user.bookings.show', $payment->booking_id)
                ->with('error', 'Phương thức thanh toán không hợp lệ.');
        }

        // Tạo mã chuyển khoản (có thể lấy từ config hoặc database)
        $bankAccount = env('BANK_ACCOUNT', '1234567890');
        $bankName = env('BANK_NAME', 'Ngân hàng ABC');
        $accountName = env('BANK_ACCOUNT_NAME', 'CÔNG TY TNHH KHÁCH SẠN');
        $bankBin = env('BANK_BIN', ''); // Mã BIN ngân hàng (nếu có)
        
        // Tạo nội dung chuyển khoản (không dấu để tránh lỗi encoding)
        $transferContent = "THANHTOAN " . $payment->booking_id . " " . $payment->transaction_id;
        
        // Loại bỏ dấu tiếng Việt khỏi tên chủ tài khoản để tránh lỗi encoding
        $accountNameNoAccent = $this->removeVietnameseAccent($accountName);
        
        // Tạo chuỗi QR code theo định dạng đơn giản (hỗ trợ nhiều app ngân hàng)
        // Format: STK|TEN_CHU_TK|SO_TIEN|NOI_DUNG
        // Sử dụng tên không dấu để tránh lỗi ISO-8859-1 encoding
        $qrData = "{$bankAccount}|{$accountNameNoAccent}|{$payment->amount}|{$transferContent}";
        
        // Nếu có BIN ngân hàng, có thể tạo VietQR format (tùy chọn)
        // $qrData = $this->generateVietQR($bankAccount, $accountNameNoAccent, $payment->amount, $transferContent, $bankBin);
        
        return view('user.payments.qr', compact('payment', 'bankAccount', 'bankName', 'accountName', 'transferContent', 'qrData'));
    }

    /**
     * Xác nhận đã chuyển khoản (cho QR chuyển khoản)
     */
    public function confirmPayment(Request $request, $paymentId)
    {
        $payment = Payment::with('booking')->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Chỉ cho phép xác nhận nếu payment status là pending và phương thức là bank_transfer_qr
        if ($payment->payment_status !== 'pending' || $payment->payment_method !== 'bank_transfer_qr') {
            return redirect()->route('user.payments.qr', $payment->id)
                ->with('error', 'Không thể xác nhận thanh toán này.');
        }

        $validated = $request->validate([
            'transaction_id' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Upload ảnh biên lai nếu có
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
            $validated['receipt_image'] = $imagePath;
        }

        // Cập nhật thông tin payment
        // Giữ status là 'pending' để admin xác nhận, hoặc có thể tự động set thành 'completed' nếu muốn
        $updateData = [
            'notes' => $validated['notes'] ?? $payment->notes,
        ];

        if (isset($validated['receipt_image'])) {
            // Xóa ảnh cũ nếu có
            if ($payment->receipt_image && Storage::disk('public')->exists($payment->receipt_image)) {
                Storage::disk('public')->delete($payment->receipt_image);
            }
            $updateData['receipt_image'] = $validated['receipt_image'];
        }

        if (!empty($validated['transaction_id'])) {
            $updateData['transaction_id'] = $validated['transaction_id'];
        }

        $payment->update($updateData);

        // Tùy chọn: Tự động cập nhật status thành 'completed' (nếu muốn tự động xác nhận)
        // Hoặc giữ 'pending' để admin xác nhận thủ công
        // $payment->update(['payment_status' => 'completed', 'payment_date' => now()]);
        // $payment->booking->update(['status' => 'confirmed']);

        return redirect()->route('user.payments.qr', $payment->id)
            ->with('success', 'Đã gửi xác nhận thanh toán! Admin sẽ kiểm tra và xác nhận trong thời gian sớm nhất.');
    }

    /**
     * Hủy thanh toán QR
     */
    public function cancelPayment(Request $request, $paymentId)
    {
        $payment = Payment::with('booking')->findOrFail($paymentId);
        
        // Kiểm tra quyền truy cập
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Chỉ cho phép hủy nếu payment method là bank_transfer_qr và status là pending hoặc failed
        if ($payment->payment_method !== 'bank_transfer_qr') {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Chỉ có thể hủy thanh toán QR chuyển khoản.');
        }

        // Không cho phép hủy nếu đã completed hoặc refunded
        if (in_array($payment->payment_status, ['completed', 'refunded'])) {
            return redirect()->route('user.payments.show', $payment->id)
                ->with('error', 'Không thể hủy thanh toán đã hoàn thành hoặc đã hoàn tiền.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:500',
        ]);

        // Kiểm tra xem đã có transaction_id hoặc receipt_image chưa
        $hasConfirmed = !empty($payment->transaction_id) || !empty($payment->receipt_image);
        
        // Nếu đã xác nhận chuyển khoản, yêu cầu lý do hủy
        if ($hasConfirmed && empty($validated['cancel_reason'])) {
            return redirect()->back()
                ->with('error', 'Vui lòng nhập lý do hủy thanh toán vì bạn đã xác nhận chuyển khoản.');
        }

        // Cập nhật payment status thành failed
        $updateData = [
            'payment_status' => 'failed',
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 
                      "Đã hủy thanh toán QR vào " . now()->format('d/m/Y H:i') . 
                      ($validated['cancel_reason'] ? ". Lý do: " . $validated['cancel_reason'] : ''),
        ];

        $payment->update($updateData);

        // Xóa ảnh biên lai nếu có (tùy chọn - có thể giữ lại để admin xem)
        // if ($payment->receipt_image && Storage::disk('public')->exists($payment->receipt_image)) {
        //     Storage::disk('public')->delete($payment->receipt_image);
        // }

        return redirect()->route('user.bookings.show', $payment->booking_id)
            ->with('success', 'Đã hủy thanh toán QR thành công. Bạn có thể chọn phương thức thanh toán khác.');
    }

    /**
     * Loại bỏ dấu tiếng Việt để tránh lỗi encoding
     */
    private function removeVietnameseAccent($str)
    {
        // Chuyển đổi sang UTF-8 nếu chưa phải
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'auto');
        }
        
        // Loại bỏ dấu tiếng Việt
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", 'y', $str);
        $str = preg_replace("/(đ)/u", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/u", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u", 'Y', $str);
        $str = preg_replace("/(Đ)/u", 'D', $str);
        
        // Loại bỏ các ký tự đặc biệt không hỗ trợ trong ISO-8859-1
        // Sử dụng TRANSLIT để chuyển đổi ký tự có dấu thành không dấu
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        
        // Loại bỏ các ký tự không phải ASCII còn sót lại
        $str = preg_replace('/[^\x00-\x7F]/', '', $str);
        
        return trim($str);
    }
}
