<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Models\Shift;
use App\Services\EmployeeNotificationService;
use App\Exports\DashboardExport;
use App\Exports\AvailableRoomsExport;
use App\Exports\OccupiedRoomsExport;
use App\Exports\RecentBookingsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $today = now();
        
        // Mặc định từ đầu tuần đến cuối tuần
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Validate: Đến ngày không được nhỏ hơn từ ngày
        if ($dateFrom && $dateTo && $dateTo < $dateFrom) {
            return redirect()->route('admin.dashboard')
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
        
        // Tạo dateRange từ dateFrom và dateTo
        $dateRange = [
            'start' => Carbon::parse($dateFrom)->startOfDay(),
            'end' => Carbon::parse($dateTo)->endOfDay(),
            'period' => 'custom'
        ];
        
        // Tính doanh thu theo khoảng thời gian đã chọn
        // Logic: Lọc booking theo khoảng thời gian (check_in/check_out), chỉ tính payment có status = 'completed'
        $filteredRevenue = 0;
        
        if ($dateRange['start'] && $dateRange['end']) {
            $startDate = $dateRange['start']->toDateString();
            $endDate = $dateRange['end']->toDateString();
            
            // Lấy các booking có check_in_date hoặc check_out_date trong khoảng thời gian
            $bookingsInRange = Booking::where(function($query) use ($startDate, $endDate) {
                // Booking có check_in_date trong khoảng
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                      // HOẶC booking có check_out_date trong khoảng
                      ->orWhereBetween('check_out_date', [$startDate, $endDate])
                      // HOẶC booking bao phủ toàn bộ khoảng thời gian
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('check_in_date', '<=', $startDate)
                            ->where('check_out_date', '>=', $endDate);
                      });
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->with('payment')
            ->get();
            
            // Tính tổng doanh thu từ các payment completed của các booking này
            $filteredRevenue = $bookingsInRange->sum(function($booking) {
                return $booking->payment && $booking->payment->payment_status === 'completed' 
                    ? $booking->payment->amount 
                    : 0;
            });
        }
        
        // Tính doanh thu theo các khoảng thời gian (cho hiển thị)
        // Nếu có filter, tính dựa trên khoảng thời gian đã chọn, nếu không thì tính dựa trên thời gian hiện tại
        $baseDate = ($dateFrom && $dateTo) ? Carbon::parse($dateTo) : $today;
        $filterStart = ($dateFrom && $dateTo) ? Carbon::parse($dateFrom) : null;
        $filterEnd = ($dateFrom && $dateTo) ? Carbon::parse($dateTo) : null;
        
        // Tính today: nếu có filter thì tính theo ngày cuối của filter, nếu không thì tính hôm nay
        $todayRevenue = 0;
        if ($filterStart && $filterEnd) {
            // Tính doanh thu của các booking có check_in/check_out trong ngày cuối của filter
            $todayBookings = Booking::where(function($query) use ($filterEnd) {
                $query->whereDate('check_in_date', $filterEnd->toDateString())
                      ->orWhereDate('check_out_date', $filterEnd->toDateString());
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->with('payment')
            ->get();
            
            $todayRevenue = $todayBookings->sum(function($booking) {
                return $booking->payment && $booking->payment->payment_status === 'completed' 
                    ? $booking->payment->amount 
                    : 0;
            });
        } else {
            $todayRevenue = Payment::where('payment_status', 'completed')
                ->where(function($query) use ($today) {
                    $query->whereDate('payment_date', $today->toDateString())
                          ->orWhere(function($q) use ($today) {
                              $q->whereNull('payment_date')
                                ->whereDate('created_at', $today->toDateString());
                          });
                })
                ->sum('amount') ?? 0;
        }
        
        // Tính this_week: nếu có filter thì tính theo khoảng filter, nếu không thì tính tuần hiện tại
        $thisWeekRevenue = 0;
        if ($filterStart && $filterEnd) {
            // Tính doanh thu của các booking trong khoảng filter
            $thisWeekRevenue = $filteredRevenue;
        } else {
            $thisWeekRevenue = Payment::where('payment_status', 'completed')
                ->where(function($query) use ($today) {
                    $query->whereBetween('payment_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ])
                    ->orWhere(function($q) use ($today) {
                        $q->whereNull('payment_date')
                          ->whereBetween('created_at', [
                              $today->copy()->startOfWeek(),
                              $today->copy()->endOfWeek()
                          ]);
                    });
                })
                ->sum('amount') ?? 0;
        }
        
        // Tính this_month: nếu có filter thì tính theo tháng của khoảng filter, nếu không thì tính tháng hiện tại
        $thisMonthRevenue = 0;
        if ($filterStart && $filterEnd) {
            // Tính doanh thu của các booking trong tháng của khoảng filter
            $monthStart = $filterStart->copy()->startOfMonth();
            $monthEnd = $filterEnd->copy()->endOfMonth();
            
            $monthBookings = Booking::where(function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('check_in_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                      ->orWhereBetween('check_out_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                      ->orWhere(function($q) use ($monthStart, $monthEnd) {
                          $q->where('check_in_date', '<=', $monthStart->toDateString())
                            ->where('check_out_date', '>=', $monthEnd->toDateString());
                      });
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->with('payment')
            ->get();
            
            $thisMonthRevenue = $monthBookings->sum(function($booking) {
                return $booking->payment && $booking->payment->payment_status === 'completed' 
                    ? $booking->payment->amount 
                    : 0;
            });
        } else {
            $thisMonthRevenue = Payment::where('payment_status', 'completed')
                ->where(function($query) use ($today) {
                    $query->where(function($q) use ($today) {
                        $q->whereYear('payment_date', $today->year)
                          ->whereMonth('payment_date', $today->month);
                    })
                    ->orWhere(function($q) use ($today) {
                        $q->whereNull('payment_date')
                          ->whereYear('created_at', $today->year)
                          ->whereMonth('created_at', $today->month);
                    });
                })
                ->sum('amount') ?? 0;
        }
        
        // Tính this_year: nếu có filter thì tính theo năm của khoảng filter, nếu không thì tính năm hiện tại
        $thisYearRevenue = 0;
        if ($filterStart && $filterEnd) {
            // Tính doanh thu của các booking trong năm của khoảng filter
            $yearStart = $filterStart->copy()->startOfYear();
            $yearEnd = $filterEnd->copy()->endOfYear();
            
            $yearBookings = Booking::where(function($query) use ($yearStart, $yearEnd) {
                $query->whereBetween('check_in_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
                      ->orWhereBetween('check_out_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
                      ->orWhere(function($q) use ($yearStart, $yearEnd) {
                          $q->where('check_in_date', '<=', $yearStart->toDateString())
                            ->where('check_out_date', '>=', $yearEnd->toDateString());
                      });
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->with('payment')
            ->get();
            
            $thisYearRevenue = $yearBookings->sum(function($booking) {
                return $booking->payment && $booking->payment->payment_status === 'completed' 
                    ? $booking->payment->amount 
                    : 0;
            });
        } else {
            $thisYearRevenue = Payment::where('payment_status', 'completed')
                ->where(function($query) use ($today) {
                    $query->whereYear('payment_date', $today->year)
                          ->orWhere(function($q) use ($today) {
                              $q->whereNull('payment_date')
                                ->whereYear('created_at', $today->year);
                          });
                })
                ->sum('amount') ?? 0;
        }
        
        $revenueStats = [
            'filtered' => $filteredRevenue,
            'today' => $todayRevenue,
            'this_week' => $thisWeekRevenue,
            'this_month' => $thisMonthRevenue,
            'this_year' => $thisYearRevenue,
            'total' => Payment::where('payment_status', 'completed')->sum('amount') ?? 0,
        ];

        // Kiểm tra phòng trống theo khoảng thời gian đã chọn
        // Nếu có filter, lọc theo khoảng thời gian, nếu không thì lọc theo hôm nay
        $filterDate = $dateFrom && $dateTo ? Carbon::parse($dateFrom)->toDateString() : $today->toDateString();
        $filterDateEnd = $dateFrom && $dateTo ? Carbon::parse($dateTo)->toDateString() : $today->toDateString();
        
        // Lấy danh sách phòng có booking active trong khoảng thời gian (tất cả)
        $allOccupiedRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($query) use ($filterDate, $filterDateEnd) {
                // Booking overlap với khoảng thời gian nếu:
                // - check_in_date <= filterDateEnd VÀ check_out_date > filterDate
                $query->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate);
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();
        
        // Lấy danh sách phòng có booking đã thanh toán thành công
        $paidRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($query) use ($filterDate, $filterDateEnd) {
                $query->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate);
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();
        
        // Phòng có khách chưa thanh toán = tất cả - đã thanh toán
        $occupiedRoomIds = array_diff($allOccupiedRoomIds, $paidRoomIds);

        // Phòng trống = available VÀ không có trong danh sách occupied
        $availableRoomsToday = Room::where('status', 'available')
            ->whereNotIn('id', $allOccupiedRoomIds)
            ->count();

        // Phòng đang có khách trong khoảng thời gian (chưa thanh toán)
        $occupiedRoomsToday = count($occupiedRoomIds);
        
        // Phòng đã thanh toán
        $paidRoomsToday = count($paidRoomIds);

        // Phòng đang bảo trì
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        $stats = [
            'total_rooms' => Room::count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'available_rooms_today' => $availableRoomsToday, // Phòng trống hôm nay
            'occupied_rooms_today' => $occupiedRoomsToday, // Phòng có khách hôm nay
            'maintenance_rooms' => $maintenanceRooms,
            'total_customers' => User::count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'checked_in_today' => Booking::where('status', 'checked_in')
                ->where('check_in_date', '<=', $filterDateEnd)
                ->where('check_out_date', '>', $filterDate)
                ->count(),
        ];

        // Lấy doanh thu theo period đã chọn cho biểu đồ
        $monthlyRevenue = $this->getMonthlyRevenueData($dateRange);
        $dailyRevenue = $this->getDailyRevenueData($dateRange);
        
        // Lấy dữ liệu bookings và payments theo filter (cho export)
        $bookingsQuery = Booking::query();
        $paymentsQuery = Payment::where('payment_status', 'completed');
        
        if ($dateRange['start'] && $dateRange['end']) {
            $bookingsQuery->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            $paymentsQuery->where(function($query) use ($dateRange) {
                $query->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']])
                      ->orWhere(function($q) use ($dateRange) {
                          $q->whereNull('payment_date')
                            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                      });
            });
        }
        
        // Stats vẫn tính tổng (không filter)

        // Thống kê trạng thái đặt phòng
        $bookingStatus = [
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'checked_in' => Booking::where('status', 'checked_in')->count(),
            'checked_out' => Booking::where('status', 'checked_out')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        // Danh sách phòng trống trong khoảng thời gian (chi tiết)
        $availableRoomsList = Room::where('status', 'available')
            ->whereNotIn('id', $allOccupiedRoomIds)
            ->with('primaryImage')
            ->orderBy('room_number')
            ->get();

        // Danh sách phòng có khách CHƯA THANH TOÁN trong khoảng thời gian (chi tiết)
        $occupiedRoomsList = Room::whereIn('id', $occupiedRoomIds)
            ->with(['primaryImage', 'bookings' => function($query) use ($filterDate, $filterDateEnd) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                      ->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate)
                      ->where(function($q) {
                          // Chưa thanh toán: không có payment hoặc payment_status != 'completed'
                          $q->whereDoesntHave('payment')
                            ->orWhereHas('payment', function($p) {
                                $p->where('payment_status', '!=', 'completed');
                            });
                      })
                      ->with(['user', 'payment']);
            }])
            ->orderBy('room_number')
            ->get();
        
        // Danh sách phòng ĐÃ THANH TOÁN trong khoảng thời gian (chi tiết)
        $paidRoomsList = Room::whereIn('id', $paidRoomIds)
            ->with(['primaryImage', 'bookings' => function($query) use ($filterDate, $filterDateEnd) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                      ->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate)
                      ->whereHas('payment', function($q) {
                          $q->where('payment_status', 'completed');
                      })
                      ->with(['user', 'payment']);
            }])
            ->orderBy('room_number')
            ->get();
        
        // Tính tổng doanh thu từ các booking đã thanh toán
        $paidRoomsRevenue = 0;
        foreach ($paidRoomsList as $room) {
            foreach ($room->bookings as $booking) {
                if ($booking->payment && $booking->payment->payment_status === 'completed') {
                    $paidRoomsRevenue += $booking->payment->amount;
                }
            }
        }

        $recentBookings = Booking::with(['user', 'room', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        // Dữ liệu để export
        $exportData = [
            'dateRange' => $dateRange,
            'revenueStats' => $revenueStats,
            'stats' => $stats,
            'bookings' => $bookingsQuery->with(['user', 'room'])->get(),
            'payments' => $paymentsQuery->with(['booking.user', 'booking.room'])->get(),
        ];

        // Kiểm tra ca làm việc cho nhân viên
        $currentShift = null;
        $hasOtherActiveShift = false;
        $otherActiveShift = null;
        $notifications = null;
        
        $admin = auth('admin')->user();
        if ($admin && $admin->isEmployee()) {
            $currentShift = Shift::where('admin_id', $admin->id)
                ->where('shift_date', Carbon::today())
                ->where('status', 'active')
                ->first();
            
            // Kiểm tra xem có nhân viên khác đang có ca active không
            $otherActiveShift = Shift::where('shift_date', Carbon::today())
                ->where('status', 'active')
                ->where('admin_id', '!=', $admin->id)
                ->with('admin')
                ->first();
            
            $hasOtherActiveShift = $otherActiveShift !== null;
            
            // Lấy thông báo cho nhân viên
            $notificationService = new EmployeeNotificationService();
            $notifications = $notificationService->getAllNotifications();
        }

        // Lấy báo cáo chi tiết ca làm việc của các nhân viên (chỉ cho admin/manager)
        $shiftReports = [];
        if (!$admin || !$admin->isEmployee()) {
            // Lấy các ca làm việc trong khoảng thời gian đã chọn
            $shiftsQuery = Shift::with(['admin', 'bookings'])
                ->whereIn('status', ['active', 'completed']);
            
            if ($dateRange['start'] && $dateRange['end']) {
                $shiftsQuery->whereBetween('shift_date', [
                    $dateRange['start']->toDateString(),
                    $dateRange['end']->toDateString()
                ]);
            } else {
                // Mặc định lấy 7 ngày gần đây
                $shiftsQuery->where('shift_date', '>=', Carbon::today()->subDays(7)->toDateString());
            }
            
            $shifts = $shiftsQuery->orderBy('shift_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();
            
            // Tính toán thống kê cho mỗi ca
            foreach ($shifts as $shift) {
                $revenueData = $shift->calculateRevenue();
                $totalRevenue = is_array($revenueData) ? ($revenueData['total_revenue'] ?? 0) : $revenueData;
                
                // Tính tổng tiền hoàn cho các booking đã hủy trong ca này
                $refundedAmount = 0;
                $cancelledBookings = $shift->bookings()->where('status', 'cancelled')->with('payment')->get();
                foreach ($cancelledBookings as $cancelledBooking) {
                    if ($cancelledBooking->payment && $cancelledBooking->payment->payment_status === 'refunded') {
                        // Lấy số tiền hoàn từ refund request hoặc payment notes
                        $refundRequest = \App\Models\RefundRequest::where('booking_id', $cancelledBooking->id)
                            ->where('status', 'completed')
                            ->first();
                        if ($refundRequest) {
                            $refundedAmount += $refundRequest->refund_amount;
                        } elseif ($cancelledBooking->payment->payment_status === 'refunded') {
                            // Nếu không có refund request, tính từ payment amount (có thể đã trừ phí hủy)
                            $refundedAmount += $cancelledBooking->payment->amount;
                        }
                    }
                }
                
                // Doanh thu thực tế = tổng doanh thu - tiền đã hoàn
                $actualRevenue = $totalRevenue - $refundedAmount;
                
                $shiftReports[] = [
                    'shift' => $shift,
                    'booking_count' => $shift->bookings()->count(),
                    'checked_in_count' => $shift->bookings()->where('status', 'checked_in')->count(),
                    'checked_out_count' => $shift->bookings()->where('status', 'completed')->count(),
                    'cancelled_count' => $shift->bookings()->where('status', 'cancelled')->count(),
                    'revenue' => $totalRevenue,
                    'refunded_amount' => $refundedAmount,
                    'actual_revenue' => $actualRevenue,
                    'cash_amount' => is_array($revenueData) ? ($revenueData['cash_amount'] ?? 0) : 0,
                    'card_amount' => is_array($revenueData) ? ($revenueData['card_amount'] ?? 0) : 0,
                    'transfer_amount' => is_array($revenueData) ? ($revenueData['transfer_amount'] ?? 0) : 0,
                ];
            }
        }
        return view('admin.dashboard', compact(
            'stats', 
            'revenueStats',
            'recentBookings', 
            'monthlyRevenue',
            'dailyRevenue',
            'bookingStatus',
            'availableRoomsList',
            'occupiedRoomsList',
            'paidRoomsList',
            'paidRoomsRevenue',
            'paidRoomsToday',
            'dateFrom',
            'dateTo',
            'dateRange',
            'exportData',
            'currentShift',
            'hasOtherActiveShift',
            'otherActiveShift',
            'notifications',
            'shiftReports'
        ));
    }

    /**
     * Lấy khoảng thời gian dựa trên period
     */
    private function getDateRange($period, $date, $dateTo = null, $year = null, $month = null, $quarter = null)
    {
        $start = null;
        $end = null;

        switch ($period) {
            case 'day':
                // Đảm bảo parse đúng format Y-m-d
                $start = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
                // Nếu có date_to thì dùng date_to, không thì dùng date
                if ($dateTo && $dateTo != $date) {
                    $end = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                } else {
                    $end = Carbon::createFromFormat('Y-m-d', $date)->endOfDay();
                }
                break;
            
            case 'week':
                // Đảm bảo parse đúng format Y-m-d
                if ($date) {
                    $dateObj = Carbon::createFromFormat('Y-m-d', $date);
                    $start = $dateObj->copy()->startOfWeek();
                    $end = $dateObj->copy()->endOfWeek();
                }
                break;
            
            case 'month':
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = Carbon::create($year, $month, 1)->endOfMonth();
                break;
            
            case 'quarter':
                $startMonth = ($quarter - 1) * 3 + 1;
                $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
                $end = Carbon::create($year, $startMonth + 2, 1)->endOfMonth();
                break;
            
            case 'year':
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = Carbon::create($year, 12, 31)->endOfYear();
                break;
            
            default: // 'all'
                $start = null;
                $end = null;
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'period' => $period
        ];
    }

    /**
     * Lấy dữ liệu doanh thu theo tháng
     */
    private function getMonthlyRevenueData($dateRange)
    {
        $data = [];
        
        if ($dateRange['start'] && $dateRange['end']) {
            $current = $dateRange['start']->copy();
            while ($current <= $dateRange['end']) {
                $revenue = Payment::where('payment_status', 'completed')
                    ->where(function($query) use ($current) {
                        $query->where(function($q) use ($current) {
                            $q->whereYear('payment_date', $current->year)
                              ->whereMonth('payment_date', $current->month);
                        })
                        ->orWhere(function($q) use ($current) {
                            $q->whereNull('payment_date')
                              ->whereYear('created_at', $current->year)
                              ->whereMonth('created_at', $current->month);
                        });
                    })
                    ->sum('amount') ?? 0;
                
                $data[] = [
                    'month' => $current->format('M/Y'),
                    'revenue' => $revenue
                ];
                
                $current->addMonth();
            }
        } else {
            // Mặc định 6 tháng gần đây
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenue = Payment::where('payment_status', 'completed')
                    ->where(function($query) use ($month) {
                        $query->where(function($q) use ($month) {
                            $q->whereYear('payment_date', $month->year)
                              ->whereMonth('payment_date', $month->month);
                        })
                        ->orWhere(function($q) use ($month) {
                            $q->whereNull('payment_date')
                              ->whereYear('created_at', $month->year)
                              ->whereMonth('created_at', $month->month);
                        });
                    })
                    ->sum('amount') ?? 0;
                $data[] = [
                    'month' => $month->format('M/Y'),
                    'revenue' => $revenue
                ];
            }
        }
        
        return $data;
    }

    /**
     * Lấy dữ liệu doanh thu theo ngày
     */
    private function getDailyRevenueData($dateRange)
    {
        $data = [];
        
        if ($dateRange['start'] && $dateRange['end']) {
            $daysDiff = $dateRange['start']->diffInDays($dateRange['end']);
            
            // Nếu > 30 ngày, lấy theo tuần
            if ($daysDiff > 30) {
                $current = $dateRange['start']->copy()->startOfWeek();
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    if ($weekEnd > $dateRange['end']) {
                        $weekEnd = $dateRange['end'];
                    }
                    
                    $revenue = Payment::where('payment_status', 'completed')
                        ->where(function($query) use ($current, $weekEnd) {
                            $query->whereBetween('payment_date', [$current, $weekEnd])
                                  ->orWhere(function($q) use ($current, $weekEnd) {
                                      $q->whereNull('payment_date')
                                        ->whereBetween('created_at', [$current, $weekEnd]);
                                  });
                        })
                        ->sum('amount') ?? 0;
                    
                    $data[] = [
                        'date' => $current->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                        'revenue' => $revenue
                    ];
                    
                    $current->addWeek();
                }
            } else {
                // Lấy theo ngày
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $revenue = Payment::where('payment_status', 'completed')
                        ->where(function($query) use ($current) {
                            $query->whereDate('payment_date', $current->toDateString())
                                  ->orWhere(function($q) use ($current) {
                                      $q->whereNull('payment_date')
                                        ->whereDate('created_at', $current->toDateString());
                                  });
                        })
                        ->sum('amount') ?? 0;
                    
                    $data[] = [
                        'date' => $current->format('d/m'),
                        'revenue' => $revenue
                    ];
                    
                    $current->addDay();
                }
            }
        } else {
            // Mặc định 7 ngày gần đây
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $revenue = Payment::where('payment_status', 'completed')
                    ->where(function($query) use ($date) {
                        $query->whereDate('payment_date', $date->toDateString())
                              ->orWhere(function($q) use ($date) {
                                  $q->whereNull('payment_date')
                                    ->whereDate('created_at', $date->toDateString());
                              });
                    })
                    ->sum('amount') ?? 0;
                $data[] = [
                    'date' => $date->format('d/m'),
                    'revenue' => $revenue
                ];
            }
        }
        
        return $data;
    }

    /**
     * Xuất Excel dashboard
     */
    public function export(Request $request)
    {
        $today = now();
        
        // Mặc định từ đầu tuần đến cuối tuần
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Nếu không có date_from và date_to, dùng mặc định (đầu tuần đến cuối tuần)
        if (!$dateFrom && !$dateTo) {
            $dateFrom = $startOfWeek->format('Y-m-d');
            $dateTo = $endOfWeek->format('Y-m-d');
        } elseif ($dateFrom && !$dateTo) {
            $dateTo = $dateFrom;
        } elseif (!$dateFrom && $dateTo) {
            $dateFrom = $dateTo;
        }
        
        // Tạo dateRange từ dateFrom và dateTo
        $dateRange = [
            'start' => Carbon::parse($dateFrom)->startOfDay(),
            'end' => Carbon::parse($dateTo)->endOfDay(),
            'period' => 'custom'
        ];
        
        // Lấy dữ liệu
        $bookingsQuery = Booking::query();
        $paymentsQuery = Payment::where('payment_status', 'completed');
        
        if ($dateRange['start'] && $dateRange['end']) {
            $bookingsQuery->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            $paymentsQuery->where(function($query) use ($dateRange) {
                $query->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']])
                      ->orWhere(function($q) use ($dateRange) {
                          $q->whereNull('payment_date')
                            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                      });
            });
        }
        
        $revenueQuery = Payment::where('payment_status', 'completed');
        if ($dateRange['start'] && $dateRange['end']) {
            $revenueQuery->where(function($query) use ($dateRange) {
                $query->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']])
                      ->orWhere(function($q) use ($dateRange) {
                          $q->whereNull('payment_date')
                            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                      });
            });
        }
        
        $exportData = [
            'dateRange' => $dateRange,
            'revenueStats' => [
                'filtered' => $revenueQuery->sum('amount') ?? 0
            ],
            'stats' => [
                'total_rooms' => Room::count(),
                'total_customers' => User::count(),
                'total_bookings' => $bookingsQuery->count(),
            ],
            'bookings' => $bookingsQuery->with(['user', 'room'])->get(),
            'payments' => $paymentsQuery->with(['booking.user', 'booking.room'])->get(),
        ];
        
        $filename = 'dashboard_' . $dateFrom . '_' . $dateTo . '_' . date('YmdHis') . '.xlsx';
        
        return Excel::download(new DashboardExport($exportData), $filename);
    }

    /**
     * Xuất Excel phòng trống hôm nay
     */
    public function exportAvailableRooms()
    {
        $today = now();
        $todayDate = $today->toDateString();
        
        // Lấy danh sách phòng có booking active hôm nay
        $occupiedRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($query) use ($todayDate) {
                $query->where('check_in_date', '<=', $todayDate)
                      ->where('check_out_date', '>', $todayDate);
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();

        // Phòng trống
        $availableRooms = Room::where('status', 'available')
            ->whereNotIn('id', $occupiedRoomIds)
            ->orderBy('room_number')
            ->get();

        $filename = 'phong_trong_hom_nay_' . date('YmdHis') . '.xlsx';
        return Excel::download(new AvailableRoomsExport($availableRooms), $filename);
    }

    /**
     * Xuất Excel phòng có khách hôm nay
     */
    public function exportOccupiedRooms()
    {
        $today = now();
        $todayDate = $today->toDateString();
        
        // Lấy danh sách phòng có booking active hôm nay VÀ đã thanh toán thành công
        $occupiedRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($query) use ($todayDate) {
                $query->where('check_in_date', '<=', $todayDate)
                      ->where('check_out_date', '>', $todayDate);
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();

        $occupiedRooms = Room::whereIn('id', $occupiedRoomIds)
            ->with(['bookings' => function($query) use ($todayDate) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                      ->where('check_in_date', '<=', $todayDate)
                      ->where('check_out_date', '>', $todayDate)
                      ->whereHas('payment', function($q) {
                          $q->where('payment_status', 'completed');
                      })
                      ->with(['user', 'payment']);
            }])
            ->orderBy('room_number')
            ->get();

        $filename = 'phong_co_khach_hom_nay_' . date('YmdHis') . '.xlsx';
        return Excel::download(new OccupiedRoomsExport($occupiedRooms), $filename);
    }

    /**
     * Xuất Excel phòng đã thanh toán
     */
    public function exportPaidRooms(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $today = now();
        
        // Nếu không có filter, dùng hôm nay
        $filterDate = $dateFrom && $dateTo ? Carbon::parse($dateFrom)->toDateString() : $today->toDateString();
        $filterDateEnd = $dateFrom && $dateTo ? Carbon::parse($dateTo)->toDateString() : $today->toDateString();
        
        // Lấy danh sách phòng có booking đã thanh toán thành công
        $paidRoomIds = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($query) use ($filterDate, $filterDateEnd) {
                $query->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate);
            })
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();

        $paidRooms = Room::whereIn('id', $paidRoomIds)
            ->with(['bookings' => function($query) use ($filterDate, $filterDateEnd) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                      ->where('check_in_date', '<=', $filterDateEnd)
                      ->where('check_out_date', '>', $filterDate)
                      ->whereHas('payment', function($q) {
                          $q->where('payment_status', 'completed');
                      })
                      ->with(['user', 'payment']);
            }])
            ->orderBy('room_number')
            ->get();

        $filename = 'phong_da_thanh_toan_' . ($dateFrom ? $dateFrom : $today->format('Y-m-d')) . '_' . ($dateTo ? $dateTo : $today->format('Y-m-d')) . '_' . date('YmdHis') . '.xlsx';
        return Excel::download(new OccupiedRoomsExport($paidRooms), $filename);
    }

    /**
     * Xuất Excel đặt phòng gần đây
     */
    public function exportRecentBookings()
    {
        $recentBookings = Booking::with(['user', 'room'])
            ->latest()
            ->take(50) // Lấy 50 booking gần nhất
            ->get();

        $filename = 'dat_phong_gan_day_' . date('YmdHis') . '.xlsx';
        return Excel::download(new RecentBookingsExport($recentBookings), $filename);
    }
}
