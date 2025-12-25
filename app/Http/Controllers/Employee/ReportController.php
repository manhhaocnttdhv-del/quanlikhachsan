<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị danh sách báo cáo ca của nhân viên
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = ShiftReport::with(['shift'])
            ->where('admin_id', $admin->id);

        // Lọc theo ngày
        if ($request->has('date') && $request->date != '') {
            $query->where('report_date', $request->date);
        } else {
            // Mặc định hiển thị 30 ngày gần nhất
            $query->where('report_date', '>=', Carbon::today()->subDays(30));
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('report_date', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        return view('employee.reports.index', compact('reports'));
    }

    /**
     * Hiển thị form tạo báo cáo ca
     */
    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $shiftId = $request->get('shift_id');
        $shift = null;

        if ($shiftId) {
            $shift = Shift::where('admin_id', $admin->id)->findOrFail($shiftId);
        } else {
            // Lấy ca hôm nay nếu có
            $shift = $admin->todayShift();
        }

        if (!$shift) {
            return redirect()->route('employee.reports.index')
                ->with('error', 'Không tìm thấy ca làm việc. Vui lòng chọn ca hoặc liên hệ admin.');
        }

        // Kiểm tra xem đã có báo cáo cho ca này chưa
        $existingReport = ShiftReport::where('shift_id', $shift->id)->first();
        if ($existingReport && $existingReport->status === 'submitted') {
            return redirect()->route('employee.reports.show', $existingReport->id)
                ->with('info', 'Báo cáo cho ca này đã được gửi.');
        }

        // Tính doanh thu tự động từ ca
        $revenue = $shift->calculateRevenue();

        return view('employee.reports.create', compact('shift', 'revenue', 'existingReport'));
    }

    /**
     * Lưu báo cáo ca
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'report_date' => 'required|date',
            'total_revenue' => 'required|numeric|min:0',
            'cash_amount' => 'required|numeric|min:0',
            'card_amount' => 'required|numeric|min:0',
            'transfer_amount' => 'required|numeric|min:0',
            'other_amount' => 'required|numeric|min:0',
            'total_checkouts' => 'required|integer|min:0',
            'paid_checkouts' => 'required|integer|min:0',
            'unpaid_checkouts' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,submitted',
        ]);

        // Kiểm tra xem shift có thuộc về nhân viên này không
        $shift = Shift::where('admin_id', $admin->id)->findOrFail($validated['shift_id']);

        // Kiểm tra xem đã có báo cáo cho ca này chưa
        $existingReport = ShiftReport::where('shift_id', $validated['shift_id'])->first();
        if ($existingReport) {
            if ($existingReport->status === 'submitted') {
                return back()->with('error', 'Báo cáo cho ca này đã được gửi. Không thể chỉnh sửa.');
            }
            // Cập nhật báo cáo draft
            $existingReport->update(array_merge($validated, ['admin_id' => $admin->id]));
            return redirect()->route('employee.reports.show', $existingReport->id)
                ->with('success', 'Cập nhật báo cáo thành công!');
        }

        // Tạo báo cáo mới
        $report = ShiftReport::create(array_merge($validated, ['admin_id' => $admin->id]));

        return redirect()->route('employee.reports.show', $report->id)
            ->with('success', $validated['status'] === 'submitted' ? 'Gửi báo cáo thành công!' : 'Lưu báo cáo thành công!');
    }

    /**
     * Hiển thị chi tiết báo cáo
     */
    public function show($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $report = ShiftReport::with(['shift', 'admin'])
            ->where('admin_id', $admin->id)
            ->findOrFail($id);

        return view('employee.reports.show', compact('report'));
    }

    /**
     * Hiển thị form chỉnh sửa báo cáo
     */
    public function edit($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $report = ShiftReport::with('shift')
            ->where('admin_id', $admin->id)
            ->findOrFail($id);

        if ($report->status === 'submitted') {
            return redirect()->route('employee.reports.show', $report->id)
                ->with('error', 'Báo cáo đã được gửi. Không thể chỉnh sửa.');
        }

        $shift = $report->shift;
        $revenue = $shift->calculateRevenue();

        return view('employee.reports.edit', compact('report', 'shift', 'revenue'));
    }

    /**
     * Cập nhật báo cáo
     */
    public function update(Request $request, $id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $report = ShiftReport::where('admin_id', $admin->id)->findOrFail($id);

        if ($report->status === 'submitted') {
            return back()->with('error', 'Báo cáo đã được gửi. Không thể chỉnh sửa.');
        }

        $validated = $request->validate([
            'total_revenue' => 'required|numeric|min:0',
            'cash_amount' => 'required|numeric|min:0',
            'card_amount' => 'required|numeric|min:0',
            'transfer_amount' => 'required|numeric|min:0',
            'other_amount' => 'required|numeric|min:0',
            'total_checkouts' => 'required|integer|min:0',
            'paid_checkouts' => 'required|integer|min:0',
            'unpaid_checkouts' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,submitted',
        ]);

        $report->update($validated);

        return redirect()->route('employee.reports.show', $report->id)
            ->with('success', $validated['status'] === 'submitted' ? 'Gửi báo cáo thành công!' : 'Cập nhật báo cáo thành công!');
    }

    /**
     * Xóa báo cáo (chỉ cho phép xóa báo cáo draft)
     */
    public function destroy($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $report = ShiftReport::where('admin_id', $admin->id)->findOrFail($id);

        // Chỉ cho phép xóa báo cáo draft
        if ($report->status === 'submitted') {
            return redirect()->route('admin.employee.reports.index')
                ->with('error', 'Không thể xóa báo cáo đã được gửi.');
        }

        $report->delete();

        return redirect()->route('admin.employee.reports.index')
            ->with('success', 'Xóa báo cáo thành công!');
    }
}
