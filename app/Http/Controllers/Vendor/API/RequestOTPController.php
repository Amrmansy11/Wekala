<?php

namespace App\Http\Controllers\Vendor\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Auth\OTPRequest;
use App\Repositories\Vendor\VendorUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;


class RequestOTPController extends Controller
{
    private int $maxAttempts = 3;
    private int $decaySeconds = 60;

    public function __construct(protected VendorUserRepository $vendorUserRepository){}

    /**
     * @param OTPRequest $request
     * @return JsonResponse
     */
    public function requestOTP(OTPRequest $request): JsonResponse
    {
        $key = 'otp-request:' . $request->string('action') . '-' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, $this->decaySeconds);
        $this->vendorUserRepository->requestOTP([
            'otp_type' => 'phone',
            'otp_value' => $request->string('phone'),
            'action' => $request->string('action'),
        ]);
        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }
}
