<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        // Cho phép cả admin và nhân viên quản lý hoàn tiền
    }

    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with(['payment', 'booking', 'user', 'processedBy']);

        // Filter theo status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter theo phương thức hoàn tiền
        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->refund_method);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhere('id', $search)
                ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        $refundRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Thống kê
        $stats = [
            'total' => RefundRequest::count(),
            'pending' => RefundRequest::pending()->count(),
            'approved' => RefundRequest::approved()->count(),
            'rejected' => RefundRequest::where('status', 'rejected')->count(),
            'completed' => RefundRequest::completed()->count(),
        ];

        return view('admin.refunds.index', compact('refundRequests', 'stats'));
    }

    /**
     * Chi tiết yêu cầu hoàn tiền
     */
    public function show($id)
    {
        $refundRequest = RefundRequest::with(['payment', 'booking', 'user', 'processedBy', 'booking.room'])
            ->findOrFail($id);

        return view('admin.refunds.show', compact('refundRequest'));
    }

    /**
     * Duyệt yêu cầu hoàn tiền
     */
    public function approve(Request $request, $id)
    {
        $refundRequest = RefundRequest::with(['payment', 'booking'])->findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt yêu cầu đang chờ xử lý.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $refundRequest->update([
                'status' => 'approved',
                'admin_notes' => $validated['admin_notes'] ?? null,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Đã duyệt yêu cầu hoàn tiền thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu hoàn tiền
     */
    public function reject(Request $request, $id)
    {
        $refundRequest = RefundRequest::with(['payment', 'booking'])->findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể từ chối yêu cầu đang chờ xử lý.');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        DB::beginTransaction();
        try {
            $refundRequest->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Hoàn thành hoàn tiền (đã chuyển tiền)
     */
    public function complete(Request $request, $id)
    {
        $refundRequest = RefundRequest::with(['payment', 'booking'])->findOrFail($id);

        if ($refundRequest->status !== 'approved') {
            return back()->with('error', 'Chỉ có thể hoàn thành yêu cầu đã được duyệt.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $refundRequest->update([
                'status' => 'completed',
                'admin_notes' => ($refundRequest->admin_notes ? $refundRequest->admin_notes . "\n" : '') . 
                    ($validated['admin_notes'] ?? 'Đã hoàn tiền thành công vào ' . now()->format('d/m/Y H:i')),
                'processed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Đã đánh dấu hoàn tiền thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}

