<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class VendorApiSwitchAccountMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (($request->is('api/vendor/*') || $request->is('api/vendor')) &&
            (Auth::check() && $request->hasHeader('vendor-id'))
        ) {
            Gate::authorize('access-switch-vendor');
        }
        return $next($request);
    }
}
