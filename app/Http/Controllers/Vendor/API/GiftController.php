<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use App\Http\Controllers\Vendor\API\VendorController;
use App\Http\Requests\Vendor\Api\Gift\GiftStoreRequest;
use App\Http\Resources\GiftResource;
use App\Repositories\Vendor\GiftRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class GiftController extends VendorController
{
    public function __construct(protected GiftRepository $giftRepository)
    {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status'); // 'active', 'archived', or null for all
        
        $query = $this->giftRepository
            ->queryWithRelations()
            ->where('vendor_id', AppHelper::getVendorId())
            ->when($status === 'active', function ($q) {
                $q->active();
            })
            ->when($status === 'archived', function ($q) {
                $q->archived();
            });

        $gifts = $query->paginate($perPage);

        return response()->json([
            'data' => GiftResource::collection($gifts),
            'pagination' => [
                'currentPage' => $gifts->currentPage(),
                'total' => $gifts->total(),
                'perPage' => $gifts->perPage(),
                'lastPage' => $gifts->lastPage(),
                'hasMorePages' => $gifts->hasMorePages(),
            ]
        ]);
    }

    public function store(GiftStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['vendor_id'] = AppHelper::getVendorId();
            $gift = $this->giftRepository->store($data);
            return response()->json(['message' => 'Gift created successfully', 'data' => new GiftResource($gift)], 201);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return response()->json([
                    'message' => 'The source product cannot repeat with the same gift product.',
                    'errors' => [
                        'source_product_id' => ['The source product cannot repeat with the same gift product.'],
                        'gift_product_id' => ['The source product cannot repeat with the same gift product.']
                    ]
                ], 422);
            }
            throw $e;
        }
    }

    public function show($id): JsonResponse
    {
        $gift = $this->giftRepository->show($id);
        return response()->json(['data' => new GiftResource($gift)]);
    }

    public function update(GiftStoreRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $gift = $this->giftRepository->update($data, $id);
            return response()->json(['message' => 'Gift updated successfully', 'data' => new GiftResource($gift)]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return response()->json([
                    'message' => 'The source product cannot repeat with the same gift product.',
                    'errors' => [
                        'source_product_id' => ['The source product cannot repeat with the same gift product.'],
                        'gift_product_id' => ['The source product cannot repeat with the same gift product.']
                    ]
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy($id): JsonResponse
    {
        $this->giftRepository->delete($id);
        return response()->json(['message' => 'Gift deleted successfully']);
    }

    public function toggleArchive($id): JsonResponse
    {
        $gift = $this->giftRepository->toggleArchive($id);
        $message = $gift->isArchived() ? 'Gift archived successfully' : 'Gift unarchived successfully';
        
        return response()->json([
            'message' => $message,
            'data' => new GiftResource($gift)
        ]);
    }
}


