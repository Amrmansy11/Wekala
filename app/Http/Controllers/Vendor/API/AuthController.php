<?php

namespace App\Http\Controllers\Vendor\API;

use App\Models\VendorUser;
use Carbon\Carbon;
use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VendorUserResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Requests\Vendor\Api\Auth\LoginRequest;
use App\Http\Requests\Vendor\Api\Auth\RestPasswordRequest;
use Illuminate\Cache\RateLimiter;

class AuthController extends Controller
{

    public function __construct(protected VendorUserRepository $vendorUserRepository) {}

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {

        /** @var VendorUser $vendor */
        $vendor = $this->vendorUserRepository
            ->login($request->string('phone'), $request->string('password'));
        if (!$vendor) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
        $tokenResult = $vendor->createToken('vendor-token');
        return response()->json([
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'data' => new VendorUserResource($vendor),
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

        $vendor = $this->vendorUserRepository->resetPassword($request->string('phone'), $request->string('password'));
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found.'], 404);
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
