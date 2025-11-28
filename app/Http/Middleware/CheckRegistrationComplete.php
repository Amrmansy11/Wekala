<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $vendor = $user->vendor;
        $hasNationalId = $vendor->hasMedia('vendor_national_id');
        $hasTaxCard    = $vendor->hasMedia('vendor_tax_card');
        if (!$user) {
            return response()->json([
                'status' => 'unauthorized',
            ], 401);
        }

        if (is_null($user->vendor_id)) {
            return response()->json([
                'status' => 'incomplete_registration',
                'step'   => 1,
            ], 409);
        }



//        if (!$hasNationalId || !$hasTaxCard) {
//            return response()->json([
//                'status' => 'incomplete_registration',
//                'step'   => 2,
//            ], 409);
//        }

        return $next($request);
    }
}
