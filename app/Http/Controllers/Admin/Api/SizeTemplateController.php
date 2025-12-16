<?php

namespace App\Http\Controllers\Admin\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SizeTemplateResource;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Repositories\Vendor\SizeTemplateRepository;
use App\Http\Requests\Admin\Api\SizeTemplate\SizesPatternsRequest;
use App\Http\Requests\Admin\Api\SizeTemplate\SizeTemplateStoreRequest;
use App\Http\Requests\Admin\Api\SizeTemplate\SizeTemplateUpdateRequest;

class SizeTemplateController extends AdminController
{
    public function __construct(protected SizeTemplateRepository $sizeTemplateRepository)
    {
        // $this->middleware('permission:vendor_sizes_templates_view')->only('index');
        // $this->middleware('permission:vendor_sizes_templates_create')->only('store');
        // $this->middleware('permission:vendor_sizes_templates_update')->only('update');
        // $this->middleware('permission:vendor_sizes_templates_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $sizeTemplates = $this->sizeTemplateRepository->query()
            ->when('type', fn($query) => $query->where('type', $request->string('type')))
            ->when('vendor_id', fn($query) => $query->where('vendor_id', $request->integer('vendor_id')))
            ->paginate($perPage);

        return response()->json([
            'data' => SizeTemplateResource::collection($sizeTemplates),
            'pagination' => [
                'currentPage' => $sizeTemplates->currentPage(),
                'total' => $sizeTemplates->total(),
                'perPage' => $sizeTemplates->perPage(),
                'lastPage' => $sizeTemplates->lastPage(),
                'hasMorePages' => $sizeTemplates->hasMorePages(),
            ]
        ]);

    }

    public function store(SizeTemplateStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sizeTemplate = $this->sizeTemplateRepository->store($data);
        return response()->json(['data' => new SizeTemplateResource($sizeTemplate)]);
    }


    public function update(SizeTemplateUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $sizeTemplateModel = $this->sizeTemplateRepository->query()
            ->where('vendor_id', $data['vendor_id'])
            ->find($id);
        if (!$sizeTemplateModel) {
            return response()->json(['message' => 'Size Template not found'], 404);
        }

        $sizeTemplate = $this->sizeTemplateRepository->update($data, $id);

        return response()->json(['data' => new SizeTemplateResource($sizeTemplate)]);
    }

    public function show($sizeTemplateId): JsonResponse
    {
        $sizeTemplate = $this->sizeTemplateRepository->query()
            ->find($sizeTemplateId);
        if (! $sizeTemplate) {
            return response()->json(['message' => 'Size Template not found'], 404);
        }

        return response()->json(['data' => new SizeTemplateResource($sizeTemplate)]);
    }


    /**
     * @throws Exception
     */
    public function destroy($sizeTemplateId): JsonResponse
    {
        $sizeTemplate = $this->sizeTemplateRepository->delete($sizeTemplateId);
        if (!$sizeTemplate) {
            return response()->json(['message' => 'Size Template not found'], 404);
        }
        return response()->json(['data' => true]);
    }

    public function getSizePatternsBySizeTemplateId(int $id, int $vendorId, SizesPatternsRequest $request): JsonResponse
    {
        $sizeTemplate = $this->sizeTemplateRepository->query()
            ->where('vendor_id', $vendorId)
            ->where('id', $id)
            ->first();
        if (! $sizeTemplate) {
            return response()->json(['message' => 'Size Template not found'], 404);
        }
        $sizePatterns = $this->sizeTemplateRepository->patterns($request->sizes, $sizeTemplate);
        return response()->json(['data' => $sizePatterns]);
    }
}
