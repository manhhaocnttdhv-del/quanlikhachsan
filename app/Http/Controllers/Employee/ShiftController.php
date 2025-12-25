<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\ShiftStatusService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    protected $shiftStatusService;

    public function __construct(ShiftStatusService $shiftStatusService)
    {
        $this->middleware('auth:admin');
        $this->shiftStatusService = $shiftStatusService;
    }

    /**
     * Hiển thị danh sách ca làm việc của nhân viên
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = Shift::where('admin_id', $admin->id);

        // Lọc theo ngày
        if ($request->has('date') && $request->date != '') {
            $query->where('shift_date', $request->date);
        } else {
            // Mặc định hiển thị từ hôm nay trở đi
            $query->where('shift_date', '>=', Carbon::today());
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $shifts = $query->orderBy('shift_date')->orderBy('start_time')->paginate(15);

        return view('employee.shifts.index', compact('shifts'));
    }

    /**
     * Hiển thị chi tiết ca làm việc
     */
    public function show($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $shift = Shift::where('admin_id', $admin->id)
            ->with(['bookings.user', 'bookings.room', 'bookings.payment'])
            ->findOrFail($id);

        // Tính doanh thu của ca
        $revenueData = $shift->calculateRevenue();
        $revenue = is_array($revenueData) ? ($revenueData['total_revenue'] ?? 0) : $revenueData;

        // Thống kê booking
        $totalBookings = $shift->bookings()->count();
        $checkedInBookings = $shift->bookings()->where('status', 'checked_in')->count();
        $checkedOutBookings = $shift->bookings()->where('status', 'checked_out')->count();

        return view('employee.shifts.show', compact('shift', 'revenue', 'totalBookings', 'checkedInBookings', 'checkedOutBookings'));
    }

    /**
     * Nhân viên tự cập nhật trạng thái ca của mình
     */
    public function updateStatus(Request $request, $id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,active,completed',
        ]);

        $success = $this->shiftStatusService->updateShiftStatusByEmployee(
            $id,
            $admin->id,
            $validated['status']
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái ca thành công!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái ca. Vui lòng kiểm tra lại.'
            ], 400);
        }
    }

    /**
     * Xóa ca làm việc (chỉ cho phép xóa ca scheduled trong tương lai)
     */
    public function destroy($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.'
            ], 403);
        }

        $shift = Shift::where('admin_id', $admin->id)->findOrFail($id);

        // Chỉ cho phép xóa ca scheduled trong tương lai
        if ($shift->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa ca đã lên lịch (scheduled) trong tương lai.'
            ], 400);
        }

        $shiftDate = Carbon::parse($shift->shift_date);
        if ($shiftDate->isPast() || $shiftDate->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa ca trong tương lai.'
            ], 400);
        }

        // Kiểm tra xem ca có đang được sử dụng không (có booking nào không)
        if ($shift->bookings()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa ca này vì đã có booking liên quan.'
            ], 400);
        }

        $shift->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa ca làm việc thành công!'
        ]);
    }
}
