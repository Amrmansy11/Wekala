<?php

namespace App\Http\Controllers\Vendor\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FeedResource;
use App\Repositories\Vendor\FeedRepository;
use App\Http\Requests\Vendor\Api\Feed\FeedStoreRequest;
use App\Http\Requests\Vendor\Api\Feed\FeedUpdateRequest;

class FeedController extends VendorController
{
    public function __construct(protected FeedRepository $feedRepository)
    {
        // $this->middleware('permission:vendor_feeds_view')->only('index');
        // $this->middleware('permission:vendor_feeds_create')->only('store');
        // $this->middleware('permission:vendor_feeds_update')->only('update');
        // $this->middleware('permission:vendor_feeds_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $feeds = $this->feedRepository->query()
            ->where('vendor_id', $id)
            ->with('products')
            ->paginate($perPage);
        return response()->json([
            'data' => FeedResource::collection($feeds),
            'pagination' => [
                'currentPage' => $feeds->currentPage(),
                'total' => $feeds->total(),
                'perPage' => $feeds->perPage(),
                'lastPage' => $feeds->lastPage(),
                'hasMorePages' => $feeds->hasMorePages(),
            ]
        ]);
    }

    public function getTodayFeeds(int $id): JsonResponse
    {
        $feeds = $this->feedRepository->query()
            ->where('vendor_id', $id)
            ->with('products')
            ->where('created_at', '>=', now()->subDay())
            ->get();
        return response()->json([
            'data' => FeedResource::collection($feeds),
        ]);
    }

    public function store(FeedStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $feed = $this->feedRepository->store([
            'vendor_id' => $data['vendor_id'],
        ]);
        if ($request->hasFile('media')) {
            $feed->addMedia($request->file('media'))
                ->usingName('feed_media_' . $feed->id)
                ->toMediaCollection('feed_media');
        }
        if(isset($data['products_ids']) > 0) {
            $feed->products()->attach($data['products_ids']);
        }
        return response()->json(['data' => new FeedResource($feed)]);
    }


    public function update(FeedUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $feed = $this->feedRepository->find($id);

        if (!$feed) {
            return response()->json(['message' => 'Feed not found'], 404);
        }

        $feed->update(['vendor_id' => $data['vendor_id']]);

        if ($request->hasFile('media')) {
            $feed->clearMediaCollection('feed_media');
            $feed->addMedia($request->file('media'))
                ->usingName('feed_media_' . $feed->id)
                ->toMediaCollection('feed_media');
        }

        $feed->products()->sync($data['products_ids']);

        return response()->json(['data' => new FeedResource($feed)]);
    }


    /**
     * @throws Exception
     */
    public function destroy($feed): JsonResponse
    {
        $feed = $this->feedRepository->delete($feed);
        if (!$feed) {
            return response()->json(['message' => 'Feed not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
