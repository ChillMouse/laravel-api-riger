<?php

namespace App\Http\Middleware;

use App\Helpers\AppHelper;
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

        $response = $next($request);

        AppHelper::instance()->logWrite($request, $response);

        return $response;
    }
}
