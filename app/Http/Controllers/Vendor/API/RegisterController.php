<?php

namespace App\Http\Controllers\Vendor\API;

use Carbon\Carbon;
use App\Helpers\AppHelper;
use App\Models\VendorUser;
use Illuminate\Validation\Rule;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VendorUserResource;
use App\Repositories\Vendor\VendorRepository;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Requests\Vendor\Api\Auth\RegisterRequest;
use App\Http\Requests\Vendor\Api\Auth\VendorRegisterFirstStepRequest;
use App\Http\Requests\Vendor\Api\Auth\VendorRegisterSecondStepRequest;


class RegisterController extends Controller
{
    public function __construct(protected VendorUserRepository $vendorUserRepository, protected VendorRepository $vendorRepository) {}

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function store(RegisterRequest $request): JsonResponse
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
        /** @var VendorUser $vendorUser */
        $vendorUser = $this->vendorUserRepository->store([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'password' => $request->string('password'),
            'main_account' => 1,
            'is_active' => 1,
            'roles' => 'Super Admin'
        ]);
        app(RateLimiter::class)->clear($key);
        $tokenResult = $vendorUser->createToken('vendor-token');
        return response()->json([
            'message' => 'Vendor User Added Successfully.',
            'data' => new VendorUserResource($vendorUser),
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @param VendorRegisterFirstStepRequest $request
     * @return JsonResponse
     */
    public function firstStep(VendorRegisterFirstStepRequest $request): JsonResponse
    {

        $vendorUser = auth()->user();
        $data = $request->validated();
        $vendor = $this->vendorRepository->store([
            'store_type' => $data['store_type'],
            'store_name' => ['en' => $data['store_name'], 'ar' => $data['store_name']],
            'phone' => $data['phone'],
            'category_id' => $data['category_id'],
            'state_id' => $data['state_id'],
            'city_id' => $data['city_id'],
            'address' => [
                'en' => $data['address'] ?? null,
                'ar' => $data['address'] ?? null,
            ],
            'description' => [
                'en' => $data['description'] ?? null,
                'ar' => $data['description'] ?? null,
            ],
            'status' => 'pending'
        ]);
        if ($request->hasFile('logo')) {
            $vendor->addMedia($request->file('logo'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_logo');
        }
        $vendorUser->update(['vendor_id' => $vendor->id]);
        return response()->json(['data' => new VendorResource($vendor)]);
    }

    /**
     * @param VendorRegisterSecondStepRequest $request
     * @return JsonResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function secondStep(VendorRegisterSecondStepRequest $request): JsonResponse
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = auth()->user();
        $vendor = $vendorUser->vendor;
        if ($request->hasFile('tax_card_file')) {
            $vendor->addMedia($request->file('tax_card_file'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_tax_card');
        }
        if ($request->hasFile('national_id_file')) {
            $vendor->addMedia($request->file('national_id_file'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_national_id');
        }
        return response()->json(['data' => new VendorUserResource($vendor)]);
    }
}
