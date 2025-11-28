<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use App\Http\Requests\Vendor\Api\Discount\DiscountStoreRequest;
use App\Http\Resources\DiscountResource;
use App\Repositories\Vendor\DiscountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends VendorController
{
    protected DiscountRepository $discountRepository;

    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status');
        $title = $request->get('title');
        $query = $this->discountRepository->query()
            ->where('vendor_id', AppHelper::getVendorId())
            ->with('products')
            ->when($status === 'active', function ($q) {
                $q->active();
            })
            ->when($status === 'archived', function ($q) {
                $q->archived();
            })
            ->when($title, function ($q) use ($title) {
                $q->where('title', 'like', "%{$title}%");
            });

        $discounts = $query->paginate($perPage);

        return response()->json([
            'data' => DiscountResource::collection($discounts),
            'pagination' => [
                'currentPage' => $discounts->currentPage(),
                'total' => $discounts->total(),
                'perPage' => $discounts->perPage(),
                'lastPage' => $discounts->lastPage(),
                'hasMorePages' => $discounts->hasMorePages(),
            ]
        ]);
    }

    /**
     * @param DiscountStoreRequest $request
     * @return JsonResponse
     */
    public function store(DiscountStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $discount = $this->discountRepository->store($data);
        return response()->json([
            'message' => 'Discount created successfully',
            'data' => new DiscountResource($discount)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $discount = $this->discountRepository->show($id);
        return response()->json(['data' => new DiscountResource($discount)]);
    }

    public function update(DiscountStoreRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $discount = $this->discountRepository->update($data, $id);
        return response()->json([
            'message' => 'Discount updated successfully',
            'data' => new DiscountResource($discount)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->discountRepository->delete($id);
        return response()->json(['message' => 'Discount deleted successfully']);
    }

    public function toggleArchive($id): JsonResponse
    {
        $discount = $this->discountRepository->toggleArchive($id);
        $message = $discount->isArchived() ? 'Discount archived successfully' : 'Discount unarchived successfully';

        return response()->json([
            'message' => $message,
            'data' => new DiscountResource($discount)
        ]);
    }
}




