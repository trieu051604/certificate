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
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

         RateLimiter::for('student-search', function ($request) {
            $ip = $request->ip();
            return Limit::perMinute(3, 5)->by($ip)
                ->response(function () {

                    return back()->with('error', 'Bạn đã tra cứu quá nhiều lần, vui lòng thử lại sau 5 phút');
                    // return response()->json([
                    //     'message' => 'Bạn đã tra cứu quá nhiều lần, vui lòng thử lại sau 1 phút.'
                    // ], 429);
                });
        });




        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
