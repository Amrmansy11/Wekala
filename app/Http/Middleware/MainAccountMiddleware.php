<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MainAccountMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $vendor = $user->vendor;
        if ($vendor && $vendor->parent_id == null) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'This action is allowed for main account only.',
            ], 403);
        }
    }
}
