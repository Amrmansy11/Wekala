<?php

namespace App\Http\Controllers\Vendor\API;

use Illuminate\Http\JsonResponse;
use App\Http\Resources\StoryResource;
use App\Repositories\Vendor\FeedRepository;
use App\Http\Requests\Vendor\Api\Story\StoryRequest;

class StoryController extends VendorController
{
    public function __construct(protected FeedRepository $feedRepository)
    {
        // $this->middleware('permission:vendor_stories_view')->only('index');
        // $this->middleware('permission:vendor_stories_create')->only('store');
        // $this->middleware('permission:vendor_stories_update')->only('update');
        // $this->middleware('permission:vendor_stories_delete')->only('destroy');
        parent::__construct();
    }



    public function store(StoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = 'story';
        $feed = $this->feedRepository->store($data);
        if ($request->hasFile('media')) {
            $feed->addMedia($request->file('media'))
                ->usingName('feed_media_' . $feed->id)
                ->toMediaCollection('feed_media');
        }
        return response()->json(['data' => new StoryResource($feed)]);
    }
}
