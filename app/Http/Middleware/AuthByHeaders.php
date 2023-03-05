<?php

namespace App\Http\Middleware;

use App\Helpers\AppHelper;
use Closure;
use Illuminate\Http\Request;

class AuthByHeaders
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

        if ($request->bearerToken() != env('BEARER_TOKEN')) {
            return redirect('api/error_auth');
        }

        if ($request->header('Host') != 'api.uzkanova.ru') {
            return redirect('api/error_auth');
        }

        if ($request->header('Accept') != 'application/json') {
            return redirect('api/error_auth');
        }

        $response = $next($request);

        return $response;
    }
}
