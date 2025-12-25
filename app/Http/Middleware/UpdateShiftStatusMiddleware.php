<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ShiftStatusService;

class UpdateShiftStatusMiddleware
{
    protected $shiftStatusService;

    public function __construct(ShiftStatusService $shiftStatusService)
    {
        $this->shiftStatusService = $shiftStatusService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ cập nhật khi có user đã đăng nhập (admin hoặc employee)
        if (auth('admin')->check()) {
            // Cập nhật trạng thái ca tự động (chạy ngầm, không ảnh hưởng performance)
            try {
                $this->shiftStatusService->updateShiftStatuses();
            } catch (\Exception $e) {
                // Bỏ qua lỗi để không ảnh hưởng đến request
            }
        }

        return $next($request);
    }
}
