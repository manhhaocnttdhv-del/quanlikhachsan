<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Role cần kiểm tra ('admin' hoặc 'manager')
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Bạn cần đăng nhập để truy cập trang này.');
        }

        // Kiểm tra role
        if ($role === 'admin' && $admin->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này. Chỉ Admin mới có quyền này.');
        }

        return $next($request);
    }
}

