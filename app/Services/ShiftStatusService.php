<?php

namespace App\Services;

use App\Models\Shift;
use Carbon\Carbon;

class ShiftStatusService
{
    /**
     * Tự động cập nhật trạng thái ca làm việc
     */
    public function updateShiftStatuses(): array
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');

        $activatedCount = 0;
        $completedCount = 0;

        // Cập nhật ca từ 'scheduled' sang 'active' khi đến giờ bắt đầu
        $shiftsToActivate = Shift::where('status', 'scheduled')
            ->whereIn('shift_date', [$today, $yesterday])
            ->get();

        foreach ($shiftsToActivate as $shift) {
            $shiftDate = $shift->shift_date->format('Y-m-d');
            $startDateTime = Carbon::parse($shiftDate . ' ' . $shift->start_time);
            
            // Xử lý ca tối (18:00 - 24:00) có thể kéo dài sang ngày hôm sau
            if ($shift->end_time == '24:00' || $shift->end_time == '00:00') {
                if ($shiftDate == $yesterday && $now->format('Y-m-d') == $today) {
                    continue;
                }
            }
            
            // Nếu đã đến giờ bắt đầu ca
            if ($now->greaterThanOrEqualTo($startDateTime)) {
                $shift->update(['status' => 'active']);
                $activatedCount++;
            }
        }

        // Cập nhật ca từ 'active' sang 'completed' khi qua giờ kết thúc
        $shiftsToComplete = Shift::where('status', 'active')
            ->whereIn('shift_date', [$today, $yesterday])
            ->get();

        foreach ($shiftsToComplete as $shift) {
            $shiftDate = $shift->shift_date->format('Y-m-d');
            $endTime = $shift->end_time;
            
            // Xử lý ca tối (24:00 = 00:00 ngày hôm sau)
            if ($endTime == '24:00' || $endTime == '00:00') {
                $endDateTime = Carbon::parse($shiftDate)->addDay()->startOfDay();
            } else {
                $endDateTime = Carbon::parse($shiftDate . ' ' . $endTime);
            }
            
            // Nếu đã qua giờ kết thúc ca
            if ($now->greaterThan($endDateTime)) {
                $shift->update(['status' => 'completed']);
                $completedCount++;
            }
        }

        return [
            'activated' => $activatedCount,
            'completed' => $completedCount,
        ];
    }

    /**
     * Nhân viên tự cập nhật trạng thái ca của mình
     */
    public function updateShiftStatusByEmployee($shiftId, $adminId, $newStatus): bool
    {
        $shift = Shift::where('id', $shiftId)
            ->where('admin_id', $adminId)
            ->first();

        if (!$shift) {
            return false;
        }

        // Chỉ cho phép cập nhật các trạng thái hợp lệ
        $allowedStatuses = ['scheduled', 'active', 'completed'];
        if (!in_array($newStatus, $allowedStatuses)) {
            return false;
        }

        // Kiểm tra logic chuyển trạng thái
        $currentStatus = $shift->status;
        
        // Không cho phép quay lại trạng thái cũ
        if ($newStatus == $currentStatus) {
            return false;
        }

        // Chỉ cho phép chuyển từ scheduled -> active -> completed
        $validTransitions = [
            'scheduled' => ['active'],
            'active' => ['completed'],
            'completed' => [], // Không thể chuyển từ completed
        ];

        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return false;
        }

        $shift->update(['status' => $newStatus]);
        return true;
    }
}

