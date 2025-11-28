<?php

namespace App\Http\Controllers\Consumer\API;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Repositories\Vendor\FeedRepository;
use App\Http\Resources\Consumer\Feed\FeedResource;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Requests\Consumer\Api\Feed\FeedStoreRequest;
use App\Http\Requests\Consumer\Api\Feed\FeedUpdateRequest;

class FeedController extends ConsumerController
{
    public function __construct(protected FeedRepository $feedRepository)
    {
        // $this->middleware('permission:vendor_feeds_view')->only('index');
        // $this->middleware('permission:vendor_feeds_create')->only('store');
        // $this->middleware('permission:vendor_feeds_update')->only('update');
        // $this->middleware('permission:vendor_feeds_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $user = Auth::guard('consumer-api')->user();
        $feeds = $this->feedRepository->query()
// to be removed after implement store from mobile            ->where('user_id', $user->id)
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



    public function store(FeedStoreRequest $request): JsonResponse
    {
        $user = Auth::guard('consumer-api')->user();
        $feed = $this->feedRepository->store([
            'user_id' => $user->id,
        ]);
        if ($request->hasFile('media')) {
            $feed->addMedia($request->file('media'))
                ->usingName('feed_media_' . $feed->id)
                ->toMediaCollection('feed_media');
        }
        return response()->json(['data' => new FeedResource($feed)]);
    }


    public function update(FeedUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $feed = $this->feedRepository
            ->query()
            ->where('user_id', Auth::guard('consumer-api')->user()->id)
            ->find($id);

        if (!$feed) {
            return response()->json(['message' => 'Feed not found'], 404);
        }

        $feed->update(['user_id' => Auth::guard('consumer-api')->user()->id]);

        if ($request->hasFile('media')) {
            $feed->clearMediaCollection('feed_media');
            $feed->addMedia($request->file('media'))
                ->usingName('feed_media_' . $feed->id)
                ->toMediaCollection('feed_media');
        }
        return response()->json(['data' => new FeedResource($feed)]);
    }


    /**
     * @throws Exception
     */
    public function destroy($feed): JsonResponse
    {
        $feed = $this->feedRepository->query()
            ->where('user_id', Auth::guard('consumer-api')->user()->id)
            ->find($feed);
        if (!$feed) {
            return response()->json(['message' => 'Feed not found'], 404);
        }
        $feed->delete();
        return response()->json(['data' => true]);
    }
}
