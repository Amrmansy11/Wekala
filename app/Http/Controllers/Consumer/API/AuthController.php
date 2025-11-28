<?php

namespace App\Http\Controllers\Consumer\API;

use Carbon\Carbon;
use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Consumer\ConsumerRepository;
use App\Http\Requests\Consumer\Api\Auth\LoginRequest;
use App\Http\Resources\Consumer\Auth\ConsumerResource;
use App\Http\Requests\Consumer\Api\Auth\RegisterRequest;
use App\Http\Requests\Consumer\Api\Auth\RestPasswordRequest;

class AuthController extends Controller
{

    public function __construct(protected ConsumerRepository $consumerRepository) {}

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {

        /** @var User $consumer */
        $consumer = $this->consumerRepository
            ->login($request->string('email_or_phone'), $request->string('password'));
        if (!$consumer) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
        $tokenResult = $consumer->createToken('consumer-token');
        return response()->json([
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'data' => new ConsumerResource($consumer),
        ]);
    }

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {

        $key = 'register|' . request()->ip();
        if ($response = AppHelper::checkRateLimit($key, 3, 5 * 60)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'verification_code' => [
                'required',
                Rule::exists('mobile_otps')
                    ->where('otp_value', $request->string('phone'))
                    ->where('otp_type', 'phone')
                    ->where('action', 'register')
                    ->where(function ($query) {
                        $query->where(
                            'expires_at',
                            '>',
                            Carbon::now()
                        );
                    })
            ],
        ]);
        if ($validator->fails()) {
            return response()
                ->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        /** @var User $consumer */
        $consumer = $this->consumerRepository->store($request->validated());
        app(RateLimiter::class)->clear($key);
        $tokenResult = $consumer->createToken('consumer-token');
        return response()->json([
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'data' => new ConsumerResource($consumer),
        ]);
    }


    /**
     * @param RestPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(RestPasswordRequest $request): JsonResponse
    {
        $key = 'reset_password|' . request()->ip();
        if ($response = AppHelper::checkRateLimit($key, 3, 5 * 60)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'verification_code' => [
                'required',
                Rule::exists('mobile_otps')
                    ->where('otp_value', $request->string('phone'))
                    ->where('otp_type', 'phone')
                    ->where('action', 'reset_password')
                    ->where(function ($query) {
                        $query->where(
                            'expires_at',
                            '>',
                            Carbon::now()
                        );
                    })
            ],
        ]);
        if ($validator->fails()) {
            return response()
                ->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $consumer = $this->consumerRepository->resetPassword($request->string('phone'), $request->string('password'));
        if (!$consumer) {
            return response()->json(['message' => 'Consumer not found.'], 404);
        }
        app(RateLimiter::class)->clear($key);
        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }



    public function logout(): JsonResponse
    {
        $user = auth()->user();
        $user->token()->revoke();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
