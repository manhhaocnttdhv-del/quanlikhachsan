<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routes = [
            // 'admin.index',
            'quantri.index',
            'quantri.create',
            'quantri.store',
            'quantri.show',
            'quantri.edit',
            'quantri.update',
            'quantri.destroy',
            'users.index',
            'users.create',
            'users.store',
            'users.show',
            'users.edit',
            'users.update',
            'users.destroy',
            // 'citizens.index',
            // 'citizens.create',
            // 'citizens.store',
            // 'citizens.show',
            // 'citizens.edit',
            // 'citizens.update',
            // 'citizens.destroy',
            // 'birth-registrations.index',
            // 'birth-registrations.create',
            // 'birth-registrations.store',
            // 'birth-registrations.show',
            // 'birth-registrations.edit',
            // 'birth-registrations.update',
            // 'birth-registrations.destroy',
            // 'absence.index',
            // 'absence.create',
            // 'absence.store',
            // 'absence.show',
            // 'absence.edit',
            // 'absence.update',
            // 'absence.destroy',
            // 'temp-residence.index',
            // 'temp-residence.create',
            // 'temp-residence.store',
            // 'temp-residence.show',
            // 'temp-residence.edit',
            // 'temp-residence.update',
            // 'temp-residence.destroy',
            // 'death.index',
            // 'death.create',
            // 'death.store',
            // 'death.show',
            // 'death.edit',
            // 'death.update',
            // 'death.destroy',
            // 'posts.index',
            // 'posts.create',
            // 'posts.store',
            // 'posts.show',
            // 'posts.edit',
            // 'posts.update',
            // 'posts.destroy',
            // 'categories.index',
            // 'categories.create',
            // 'categories.store',
            // 'categories.show',
            // 'categories.edit',
            // 'categories.update',
            // 'categories.destroy',
            // 'noti.index',
            // 'noti.create',
            // 'noti.store',
            // 'noti.show',
            // 'noti.edit',
            // 'noti.update',
            // 'noti.destroy',
            // 'users.import',
            'setting.index',
            'setting.create',
            'setting.store',
            'setting.show',
            'setting.edit',
            'setting.update',
            'setting.destroy',
            // 'comments.index',
            // 'comments.create',
            // 'comments.store',
            // 'comments.show',
            // 'comments.edit',
            // 'comments.update',
            // 'comments.destroy',
        ];

        // Kiểm tra quyền truy cập
        if (Auth::check() && Auth::user()->role == 2) {
            foreach ($routes as $route) {
                if ($request->routeIs($route)) {
                    // Chuyển hướng đến trang 404
                    abort(404, 'Bạn không có quyền truy cập vào trang này.');
                }
            }
        }

        return $next($request);
    }


}
