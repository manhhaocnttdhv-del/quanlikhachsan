<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const ADMIN = '/admin';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Định nghĩa giới hạn tốc độ (Rate Limiting)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Load các file route
        $this->routes(function () {
            // API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Nếu bạn muốn phân tách map, bạn có thể sử dụng hàm này,
     * tuy nhiên cách hiện tại không cần phương thức map.
     */
    public function map()
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Định nghĩa Admin Routes.
     */
    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
            ->middleware('web')
            ->namespace('App\Http\Controllers\Admin')
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Định nghĩa Web Routes.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Định nghĩa API Routes.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
