<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role.admin:admin');
    }

    /**
     * Hiển thị danh sách ca làm việc
     */
    public function index(Request $request)
    {
        $query = Shift::with('admin');

        // Lọc theo nhân viên
        if ($request->has('admin_id') && $request->admin_id != '') {
            $query->where('admin_id', $request->admin_id);
        }

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
        $employees = Admin::where('role', 'employee')->get();

        return view('admin.shifts.index', compact('shifts', 'employees'));
    }

    /**
     * Hiển thị form tạo ca mới
     */
    public function create()
    {
        $employees = Admin::where('role', 'employee')->get();
        return view('admin.shifts.create', compact('employees'));
    }

    /**
     * Hiển thị form phân công theo tháng
     */
    public function createMonthly()
    {
        $employees = Admin::where('role', 'employee')->get();
        return view('admin.shifts.create-monthly', compact('employees'));
    }

    /**
     * Lưu phân công theo tháng
     */
    public function storeMonthly(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'month' => 'required|date_format:Y-m',
            'shift_types' => 'required|array|min:1',
            'shift_types.*' => 'required|in:morning,afternoon,evening',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Kiểm tra xem nhân viên có phải employee không
        $admin = Admin::findOrFail($validated['admin_id']);
        if ($admin->role !== 'employee') {
            return back()
                ->withInput()
                ->withErrors(['admin_id' => 'Chỉ có thể phân công ca cho nhân viên.']);
        }

        // Lấy năm và tháng
        $year = Carbon::parse($validated['month'])->year;
        $month = Carbon::parse($validated['month'])->month;
        
        // Lấy số ngày trong tháng
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        
        $created = 0;
        $skipped = 0;
        
        // Tạo ca cho từng ngày trong tháng
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $shiftDate = Carbon::create($year, $month, $day);
            
            // Bỏ qua các ngày trong quá khứ (trừ hôm nay)
            if ($shiftDate->isPast() && !$shiftDate->isToday()) {
                continue;
            }
            
            // Tạo ca cho mỗi loại ca được chọn
            foreach ($validated['shift_types'] as $shiftType) {
                // Kiểm tra xem ca đã tồn tại chưa
                $exists = Shift::where('admin_id', $validated['admin_id'])
                    ->where('shift_date', $shiftDate->format('Y-m-d'))
                    ->where('shift_type', $shiftType)
                    ->where('status', '!=', 'cancelled')
                    ->exists();
                
                if (!$exists) {
                    $shiftTimes = Shift::getShiftTimes($shiftType);
                    
                    Shift::create([
                        'admin_id' => $validated['admin_id'],
                        'shift_date' => $shiftDate->format('Y-m-d'),
                        'shift_type' => $shiftType,
                        'start_time' => $shiftTimes['start_time'],
                        'end_time' => $shiftTimes['end_time'],
                        'status' => 'scheduled',
                        'notes' => $validated['notes'],
                    ]);
                    
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }
        
        $message = "Phân công thành công {$created} ca";
        if ($skipped > 0) {
            $message .= ", bỏ qua {$skipped} ca đã tồn tại";
        }
        $message .= "!";
        
        return redirect()->route('admin.shifts.index')
            ->with('success', $message);
    }

    /**
     * Lưu ca mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'shift_date' => 'required|date|after_or_equal:today',
            'shift_type' => 'required|in:morning,afternoon,evening',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Tự động set giờ bắt đầu và kết thúc dựa trên shift_type
        $shiftTimes = \App\Models\Shift::getShiftTimes($validated['shift_type']);
        $validated['start_time'] = $shiftTimes['start_time'];
        $validated['end_time'] = $shiftTimes['end_time'];

        // Tự động set trạng thái là 'scheduled' khi phân công mới
        $validated['status'] = 'scheduled';

        // Kiểm tra xem nhân viên có phải employee không
        $admin = Admin::findOrFail($validated['admin_id']);
        if ($admin->role !== 'employee') {
            return back()
                ->withInput()
                ->withErrors(['admin_id' => 'Chỉ có thể phân công ca cho nhân viên.']);
        }

        // Kiểm tra trùng ca (cùng nhân viên, cùng ngày, cùng shift_type)
        $overlapping = Shift::where('admin_id', $validated['admin_id'])
            ->where('shift_date', $validated['shift_date'])
            ->where('shift_type', $validated['shift_type'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($overlapping) {
            $shiftNames = [
                'morning' => 'Ca sáng',
                'afternoon' => 'Ca trưa',
                'evening' => 'Ca tối',
            ];
            return back()
                ->withInput()
                ->withErrors(['shift_type' => 'Nhân viên này đã có ' . ($shiftNames[$validated['shift_type']] ?? 'ca này') . ' trong ngày này.']);
        }

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Phân công ca làm việc thành công!');
    }

    /**
     * Hiển thị chi tiết ca
     */
    public function show($id)
    {
        $shift = Shift::with('admin')->findOrFail($id);
        return view('admin.shifts.show', compact('shift'));
    }

    /**
     * Hiển thị form chỉnh sửa ca
     */
    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        $employees = Admin::where('role', 'employee')->get();
        return view('admin.shifts.edit', compact('shift', 'employees'));
    }

    /**
     * Cập nhật ca
     */
    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'shift_date' => 'required|date',
            'shift_type' => 'required|in:morning,afternoon,evening',
            'status' => 'required|in:scheduled,active,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Tự động set giờ bắt đầu và kết thúc dựa trên shift_type
        $shiftTimes = \App\Models\Shift::getShiftTimes($validated['shift_type']);
        $validated['start_time'] = $shiftTimes['start_time'];
        $validated['end_time'] = $shiftTimes['end_time'];

        // Kiểm tra xem nhân viên có phải employee không
        $admin = Admin::findOrFail($validated['admin_id']);
        if ($admin->role !== 'employee') {
            return back()
                ->withInput()
                ->withErrors(['admin_id' => 'Chỉ có thể phân công ca cho nhân viên.']);
        }

        // Kiểm tra trùng ca (cùng nhân viên, cùng ngày, cùng shift_type, trừ ca hiện tại)
        $overlapping = Shift::where('admin_id', $validated['admin_id'])
            ->where('shift_date', $validated['shift_date'])
            ->where('shift_type', $validated['shift_type'])
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $id)
            ->exists();

        if ($overlapping) {
            return back()
                ->withInput()
                ->withErrors(['shift_type' => 'Nhân viên này đã có ca này trong ngày.']);
        }

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Cập nhật ca làm việc thành công!');
    }

    /**
     * Xóa ca
     */
    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Xóa ca làm việc thành công!');
    }
}
