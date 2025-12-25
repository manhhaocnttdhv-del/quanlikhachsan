<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\EmployeeNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(EmployeeNotificationService $notificationService)
    {
        $this->middleware('auth:admin');
        $this->notificationService = $notificationService;
    }

    /**
     * Gửi email thông báo cho một booking cụ thể
     */
    public function sendEmail(Request $request, $bookingId)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'type' => 'required|in:check_in_reminder,check_out_reminder,unpaid_reminder',
        ]);

        $booking = Booking::with('user')->findOrFail($bookingId);

        $sent = $this->notificationService->sendEmailNotification($booking, $validated['type']);

        if ($sent) {
            return redirect()->back()
                ->with('success', 'Đã gửi email thông báo thành công đến ' . $booking->user->email);
        } else {
            return redirect()->back()
                ->with('error', 'Không thể gửi email. Vui lòng kiểm tra cấu hình email.');
        }
    }

    /**
     * Gửi email hàng loạt
     */
    public function sendBulk(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'type' => 'required|in:check_in_reminder,check_out_reminder,unpaid_reminder',
        ]);

        $sent = 0;
        $message = '';

        switch ($validated['type']) {
            case 'check_in_reminder':
                $sent = $this->notificationService->sendCheckInReminders();
                $message = "Đã gửi {$sent} email nhắc nhở check-in hôm nay.";
                break;
            case 'check_out_reminder':
                $sent = $this->notificationService->sendCheckOutReminders();
                $message = "Đã gửi {$sent} email nhắc nhở check-out hôm nay.";
                break;
            case 'unpaid_reminder':
                $sent = $this->notificationService->sendUnpaidReminders();
                $message = "Đã gửi {$sent} email nhắc nhở thanh toán.";
                break;
        }

        return redirect()->back()
            ->with('success', $message);
    }
}

