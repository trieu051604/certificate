<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $isCheckUser = count(config('ip.email')) == 0 || (count(config('ip.email')) != 0 && in_array(auth()->user()->email, config('ip.email')));
        $isCheckIPBlacklink = count(config('ip.blacklist')) == 0 || (count(config('ip.blacklist')) > 0 && !in_array($request->ip(), config('ip.blacklist')));
        $isCheckIPWhitelink = count(config('ip.whitelist')) == 0 || (count(config('ip.whiletist')) > 0 && in_array($request->ip(), config('ip.whilelist')));
        $isApiRole = auth()->user()->type === 'API';

        if ($isCheckIPBlacklink && $isCheckIPWhitelink && $isCheckUser && $isApiRole) {
            return $next($request);
        } else {
            return response()->json([
                'code' => 401,
                'msg' => 'Lỗi authentication',
            ]);
        }
    }
}
