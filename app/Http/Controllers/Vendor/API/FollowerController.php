<?php

namespace App\Http\Controllers\Vendor\API;

use App\Models\Vendor;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FollowResource;
use App\Repositories\Vendor\VendorRepository;

class FollowerController extends VendorController
{
    public function __construct(protected VendorRepository $vendorRepository)
    {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $type = $request->get('type', 'following'); // default => following

        /** @var Vendor $vendor */
        $vendor = $this->vendorRepository->find(AppHelper::getVendorId());

        $relation = $type === 'followers' ? 'followers' : 'following';

        $follows = $vendor->$relation()->paginate($perPage);

        return response()->json([
            'data' => FollowResource::collection($follows),
            'pagination' => [
                'currentPage'   => $follows->currentPage(),
                'total'         => $follows->total(),
                'perPage'       => $follows->perPage(),
                'lastPage'      => $follows->lastPage(),
                'hasMorePages'  => $follows->hasMorePages(),
            ]
        ]);
    }




    public function store(int $id): JsonResponse
    {
        $followerId = auth()->user()->vendor_id;
        $store = $this->vendorRepository->find($id);

        if (!$store) {
            return response()->json([
                'message' => 'Store not found',
            ]);
        }
        if ($store->id === $followerId) {
            return response()->json([
                'message' => 'You cannot follow yourself',
            ], 400);
        }
        if ($store->followers()->where('follower_id', $followerId)->exists()) {
            $store->followers()->detach($followerId);
            return response()->json([
                'data' => null,
                'message' => 'Unfollowed successfully',
            ]);
        }

        $store->followers()->attach($followerId);

        return response()->json([
            'data' => null,
            'message' => 'Followed successfully',
        ]);
    }
}
