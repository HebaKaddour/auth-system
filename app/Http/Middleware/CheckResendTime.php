<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckResendTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        $lastResendTime = Cache::get($ipAddress . '_last_resend_time');

        if ($lastResendTime && time() - $lastResendTime < 60*3){
            return response()->json(['message' => 'Please wait 3 minutes before requesting a resend.'], 429);
        }

        Cache::put($ipAddress . '_last_resend_time', time(), 180); // تخزين وقت إعادة إرسال الرمز

        return $next($request);
    }

}
