<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Consumer\ConsumerRepository;
use App\Http\Requests\Consumer\Api\Auth\OTPRequest;


class RequestOTPController extends Controller
{
    private int $maxAttempts = 3;
    private int $decaySeconds = 60;

    public function __construct(protected ConsumerRepository $consumerRepository) {}

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
        $this->consumerRepository->requestOTP([
            'otp_type' => 'phone',
            'otp_value' => $request->string('phone'),
            'action' => $request->string('action'),
        ]);
        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }
}
