<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\Admin;
use Carbon\Carbon;

class ShiftAutoCreateService
{
    /**
     * Tự động tạo ca làm việc khi nhân viên login
     * Logic: 
     * - Nếu chưa có ca active nào (của bất kỳ nhân viên nào) hôm nay, tạo ca mới cho nhân viên đầu tiên
     * - Nếu đã có ca active, nhân viên thứ 2 không tạo ca mới
     */
    public function createShiftOnLogin(Admin $admin): ?Shift
    {
        // Chỉ tạo ca cho nhân viên
        if (!$admin->isEmployee()) {
            return null;
        }

        $today = Carbon::today();
        
        // Kiểm tra xem nhân viên này đã có ca active hôm nay chưa
        $existingShift = Shift::where('admin_id', $admin->id)
            ->where('shift_date', $today)
            ->whereIn('status', ['scheduled', 'active'])
            ->first();

        if ($existingShift) {
            // Nếu có ca scheduled, tự động chuyển sang active
            if ($existingShift->status === 'scheduled') {
                $existingShift->update(['status' => 'active']);
            }
            return $existingShift;
        }

        // Kiểm tra xem đã có ca active nào hôm nay chưa (của bất kỳ nhân viên nào)
        $activeShiftToday = Shift::where('shift_date', $today)
            ->where('status', 'active')
            ->first();

        // Nếu đã có ca active, nhân viên thứ 2 không tạo ca mới
        if ($activeShiftToday) {
            return null;
        }

        // Chưa có ca active nào, tạo ca mới cho nhân viên đầu tiên login
        // Xác định loại ca dựa trên giờ hiện tại
        $currentHour = Carbon::now()->hour;
        $shiftType = 'morning'; // Mặc định
        
        if ($currentHour >= 6 && $currentHour < 12) {
            $shiftType = 'morning';
        } elseif ($currentHour >= 12 && $currentHour < 18) {
            $shiftType = 'afternoon';
        } elseif ($currentHour >= 18 || $currentHour < 6) {
            $shiftType = 'evening';
        }

        // Lấy giờ bắt đầu và kết thúc
        $shiftTimes = Shift::getShiftTimes($shiftType);
        
        // Tạo ca mới với status active
        $shift = Shift::create([
            'admin_id' => $admin->id,
            'shift_date' => $today,
            'shift_type' => $shiftType,
            'start_time' => $shiftTimes['start_time'],
            'end_time' => $shiftTimes['end_time'],
            'status' => 'active',
            'notes' => 'Ca tự động tạo khi nhân viên đăng nhập',
        ]);

        return $shift;
    }

    /**
     * Đóng ca của nhân viên khi logout
     */
    public function closeShiftOnLogout(Admin $admin): void
    {
        if (!$admin->isEmployee()) {
            return;
        }

        $today = Carbon::today();
        
        // Đóng ca active của nhân viên này
        Shift::where('admin_id', $admin->id)
            ->where('shift_date', $today)
            ->where('status', 'active')
            ->update(['status' => 'completed']);
    }
}
