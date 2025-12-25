<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftReport;
use App\Models\Shift;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị danh sách báo cáo ca và doanh thu
     */
    public function index(Request $request)
    {
        $query = ShiftReport::with(['shift.admin', 'admin']);

        // Mặc định từ đầu tuần đến cuối tuần
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Validate: Đến ngày không được nhỏ hơn từ ngày
        if ($dateFrom && $dateTo && $dateTo < $dateFrom) {
            return redirect()->route('admin.shift-reports.index')
                ->withErrors(['date_to' => 'Đến ngày không được nhỏ hơn từ ngày.'])
                ->withInput();
        }
        
        // Nếu không có date_from và date_to, dùng mặc định (đầu tuần đến cuối tuần)
        if (!$dateFrom && !$dateTo) {
            $dateFrom = $startOfWeek->format('Y-m-d');
            $dateTo = $endOfWeek->format('Y-m-d');
        } elseif ($dateFrom && !$dateTo) {
            // Nếu chỉ có date_from, set date_to = date_from
            $dateTo = $dateFrom;
        } elseif (!$dateFrom && $dateTo) {
            // Nếu chỉ có date_to, set date_from = date_to
            $dateFrom = $dateTo;
        }

        // Lọc theo nhân viên
        if ($request->has('admin_id') && $request->admin_id != '') {
            $query->where('admin_id', $request->admin_id);
        }

        // Lọc theo khoảng ngày
        if ($dateFrom) {
            $query->where('report_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('report_date', '<=', $dateTo);
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('report_date', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        // Thống kê tổng hợp
        $statsQuery = ShiftReport::where('status', 'submitted');
        
        if ($request->has('admin_id') && $request->admin_id != '') {
            $statsQuery->where('admin_id', $request->admin_id);
        }
        
        // Áp dụng cùng filter ngày cho stats
        if ($dateFrom) {
            $statsQuery->where('report_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $statsQuery->where('report_date', '<=', $dateTo);
        }

        $totalRevenue = $statsQuery->sum('total_revenue');
        $totalCash = $statsQuery->sum('cash_amount');
        $totalCard = $statsQuery->sum('card_amount');
        $totalTransfer = $statsQuery->sum('transfer_amount');
        $totalCheckouts = $statsQuery->sum('total_checkouts');
        $totalPaidCheckouts = $statsQuery->sum('paid_checkouts');

        $employees = Admin::where('role', 'employee')->get();

        return view('admin.shift-reports.index', compact(
            'reports',
            'employees',
            'totalRevenue',
            'totalCash',
            'totalCard',
            'totalTransfer',
            'totalCheckouts',
            'totalPaidCheckouts',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Hiển thị chi tiết báo cáo ca
     */
    public function show($id)
    {
        $report = ShiftReport::with(['shift.admin', 'admin'])->findOrFail($id);
        return view('admin.shift-reports.show', compact('report'));
    }

    /**
     * Xem doanh thu theo nhân viên
     */
    public function byEmployee(Request $request)
    {
        $query = ShiftReport::with(['admin', 'shift'])
            ->where('status', 'submitted');

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('report_date', '>=', $request->date_from);
            if ($request->has('date_to') && $request->date_to != '') {
                $query->where('report_date', '<=', $request->date_to);
            }
        } else {
            // Mặc định tháng này
            $query->whereMonth('report_date', Carbon::now()->month)
                  ->whereYear('report_date', Carbon::now()->year);
        }

        // Nhóm theo nhân viên
        $reportsByEmployee = $query->get()->groupBy('admin_id');

        $employeeStats = [];
        foreach ($reportsByEmployee as $adminId => $reports) {
            $admin = Admin::find($adminId);
            if ($admin) {
                $employeeStats[] = [
                    'admin' => $admin,
                    'total_revenue' => $reports->sum('total_revenue'),
                    'total_cash' => $reports->sum('cash_amount'),
                    'total_card' => $reports->sum('card_amount'),
                    'total_transfer' => $reports->sum('transfer_amount'),
                    'total_checkouts' => $reports->sum('total_checkouts'),
                    'total_paid_checkouts' => $reports->sum('paid_checkouts'),
                    'report_count' => $reports->count(),
                ];
            }
        }

        // Sắp xếp theo doanh thu giảm dần
        usort($employeeStats, function($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        return view('admin.shift-reports.by-employee', compact('employeeStats'));
    }

    /**
     * Xem doanh thu theo ngày
     */
    public function byDate(Request $request)
    {
        $query = ShiftReport::where('status', 'submitted');

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('report_date', '>=', $request->date_from);
            if ($request->has('date_to') && $request->date_to != '') {
                $query->where('report_date', '<=', $request->date_to);
            }
        } else {
            // Mặc định 30 ngày gần nhất
            $query->where('report_date', '>=', Carbon::today()->subDays(30));
        }

        // Nhóm theo ngày
        $reportsByDate = $query->get()->groupBy(function($report) {
            return $report->report_date->format('Y-m-d');
        });

        $dateStats = [];
        foreach ($reportsByDate as $date => $reports) {
            $dateStats[] = [
                'date' => $date,
                'total_revenue' => $reports->sum('total_revenue'),
                'total_cash' => $reports->sum('cash_amount'),
                'total_card' => $reports->sum('card_amount'),
                'total_transfer' => $reports->sum('transfer_amount'),
                'total_checkouts' => $reports->sum('total_checkouts'),
                'total_paid_checkouts' => $reports->sum('paid_checkouts'),
                'report_count' => $reports->count(),
            ];
        }

        // Sắp xếp theo ngày giảm dần
        usort($dateStats, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return view('admin.shift-reports.by-date', compact('dateStats'));
    }
}
