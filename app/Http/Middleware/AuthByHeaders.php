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
    private function redirectToError(Request $request) {
        return redirect()->action('api/error_auth', [$request]);
    }

    public function handle(Request $request, Closure $next)
    {

        if ($request->bearerToken() != env('BEARER_TOKEN') or
            $request->header('Host') != 'api.uzkanova.ru' or
            $request->header('Accept') != 'application/json'
        ) {
            return $this->redirectToError($request);
        }

        $response = $next($request);

        return $response;
    }
}
