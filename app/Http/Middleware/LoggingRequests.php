<?php

namespace App\Http\Middleware;

use App\Models\LogReq;
use Closure;
use Illuminate\Http\Request;

class LoggingRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $logreq = new LogReq();
        $logreq->url = $request->url();
        $logreq->save();
        return $next($request);
    }
}
