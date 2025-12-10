<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SliderResource;
use App\Http\Resources\SliderShowResource;
use App\Repositories\Admin\SliderRepository;
use App\Repositories\Admin\ElwekalaCollectionRepository;
use App\Http\Requests\Admin\Api\Sliders\SliderStoreRequest;
use App\Http\Requests\Admin\Api\Sliders\SliderUpdateRequest;

class SliderController extends AdminController
{
    public function __construct(protected SliderRepository $sliderRepository, protected ElwekalaCollectionRepository $elwekalaCollectionRepository)
    {
        //        $this->middleware('permission:sliders_view')->only('index');
        //        $this->middleware('permission:sliders_create')->only('store');
        //        $this->middleware('permission:sliders_update')->only('update');
        //        $this->middleware('permission:sliders_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $type = $request->string('type');
        $sliders = $this->sliderRepository->query()->with('products')
            ->when(
                $search,
                fn($query) =>
                $query->where('name', 'like', "%{$search}%")
            )
            ->when(
                $type,
                fn($query) =>
                $query->where('type', $type)
            )
            ->when(
                $request->has('is_active') && in_array($request->input('is_active'), [0, 1, '0', '1'], true),
                function ($query) use ($request) {
                    $query->where('is_active', (int) $request->input('is_active'));
                }
            )
            ->paginate($perPage);
        return response()->json([
            'data' => SliderResource::collection($sliders),
            'pagination' => [
                'currentPage' => $sliders->currentPage(),
                'total' => $sliders->total(),
                'perPage' => $sliders->perPage(),
                'lastPage' => $sliders->lastPage(),
                'hasMorePages' => $sliders->hasMorePages(),
            ]
        ]);
    }

    public function store(SliderStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = $data['type_elwekala'];
        $slider = $this->sliderRepository->store($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $slider->addMedia($image)
                    ->usingName($data['name'])
                    ->toMediaCollection('images');
            }
        }

        if ($request->has('product_ids')) {
            $slider->products()->sync($request->input('product_ids'));
        }

        return response()->json(['data' => new SliderResource($slider)]);
    }

    public function show($slider): JsonResponse
    {
        $slider = $this->sliderRepository->with(['products'])->find($slider);
        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }
        return response()->json(['data' => new SliderShowResource($slider)]);
    }

    public function update(SliderUpdateRequest $request, $sliderId): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = $data['type_elwekala'];
        $slider = $this->sliderRepository->find($sliderId);

        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }

        if ($request->filled('remove_media_ids') && is_array($request->remove_media_ids)) {
            foreach ($request->remove_media_ids as $mediaId) {
                $media = $slider->media()->whereKey($mediaId)->first();
                if ($media) {
                    $media->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $slider->addMedia($image)
                    ->usingName($data['name'])
                    ->toMediaCollection('images');
            }
        }

        if ($request->filled('product_ids')) {
            $slider->products()->sync($request->input('product_ids'));
        }

        $slider = $this->sliderRepository->update($data, $slider->id);

        // 6. رجع الريسورس
        return response()->json([
            'data' => new SliderResource($slider),
            'message' => 'Slider updated successfully'
        ]);
    }

    /**
     * @throws Exception
     */
    public function destroy($slider): JsonResponse
    {
        $slider = $this->sliderRepository->delete($slider);
        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }
        return response()->json(['data' => true]);
    }

    public function toggleIsActive($slider): JsonResponse
    {
        $slider = $this->sliderRepository->toggleIsActive($slider);
        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }
        return response()->json(['data' => new SliderResource($slider)]);
    }
}
