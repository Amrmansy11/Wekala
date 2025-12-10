<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Admin\FlashSaleResource;
use App\Repositories\Admin\FlashSaleRepository;
use App\Http\Requests\Admin\Api\FlashSale\FlashSaleStoreRequest;
use App\Http\Requests\Admin\Api\FlashSale\FlashSaleUpdateRequest;


class FlashSaleController extends AdminController
{
    public function __construct(protected FlashSaleRepository $flashSaleRepository)
    {
        $this->middleware('permission:flash_sales_view')->only('index');
        $this->middleware('permission:flash_sales_create')->only('store');
        $this->middleware('permission:flash_sales_update')->only('update');
        $this->middleware('permission:flash_sales_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $type_elwekala = $request->string('type_elwekala', 'seller');
        $flashSales = $this->flashSaleRepository->query()->where('type', 'flash_sale')->where('type_elwekala', $type_elwekala)->withWhereHas('product', fn($query) => $query->with('variants'))->paginate($perPage);
        return response()->json([
            'data' => FlashSaleResource::collection($flashSales),
            'pagination' => [
                'currentPage' => $flashSales->currentPage(),
                'total' => $flashSales->total(),
                'perPage' => $flashSales->perPage(),
                'lastPage' => $flashSales->lastPage(),
                'hasMorePages' => $flashSales->hasMorePages(),
            ]
        ]);
    }


    // public function store(FlashSaleStoreRequest $request): JsonResponse
    // {
    //     $data = $request->validated();
    //     $flashSale = $this->flashSaleRepository->store($data);
    //     return response()->json([
    //         'data' => new FlashSaleResource($flashSale)
    //     ]);
    // }

    public function store(FlashSaleStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['type'] = 'flash_sale';
        $createdItems = [];

        foreach ($validated['product_id'] as $productId) {
            $createdItems[] = $this->flashSaleRepository->store([
                'type' => $validated['type'],
                'product_id' => $productId,
                'type_elwekala' => $validated['type_elwekala'],
            ]);
        }

        return response()->json([
            'data' => null,
            'message' => 'Flash Sale created successfully',
        ]);
    }

    public function show($flashSale): JsonResponse
    {
        $flashSale = $this->flashSaleRepository->find($flashSale);
        if (!$flashSale) {
            return response()->json(['message' => 'Flash Sale not found'], 404);
        }
        return response()->json(['data' => new FlashSaleResource($flashSale)]);
    }

    // public function update(FlashSaleUpdateRequest $request, $flashSale): JsonResponse
    // {
    //     $flashSale = $this->flashSaleRepository->query()->where('type', 'flash_sale')->find($flashSale);
    //     if (!$flashSale) {
    //         return response()->json(['message' => 'Flash Sale not found'], 404);
    //     }
    //     $flashSale = $this->flashSaleRepository->update($request->validated(), $flashSale->id);
    //     return response()->json(['data' => new FlashSaleResource($flashSale)]);
    // }

    public function update(FlashSaleUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['type'] = 'flash_sale';
        $this->flashSaleRepository->query()->where('type', $validated['type'])->where('type_elwekala', $validated['type_elwekala'])->delete();

        $createdItems = [];

        foreach ($validated['product_id'] as $productId) {
            $createdItems[] = $this->flashSaleRepository->store([
                'type'       => $validated['type'],
                'product_id' => $productId,
                'type_elwekala' => $validated['type_elwekala'],
            ]);
        }
        return response()->json([
            'data'    => null,
            'message' => 'Collection updated successfully',
        ]);
    }
    /**
     * @throws Exception
     */
    public function destroy($type, $type_elwekala): JsonResponse
    {
        $deleted = $this->flashSaleRepository->query()->where('type', $type)->where('type_elwekala', $type_elwekala)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Flash Sale not found'], 404);
        }
        $this->flashSaleRepository->delete($type);
        return response()->json(['data' => true, 'message' => 'Flash Sale deleted successfully']);
    }
}
