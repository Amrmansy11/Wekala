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
        $flashSales = $this->flashSaleRepository->query()->where('type', 'flash_sale')->withWhereHas('product', fn($query) => $query->with('variants'))->paginate($perPage);
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


    public function store(FlashSaleStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = 'flash_sale';
        $flashSale = $this->flashSaleRepository->store($data);
        return response()->json([
            'data' => new FlashSaleResource($flashSale)
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

    public function update(FlashSaleUpdateRequest $request, $flashSale): JsonResponse
    {
        $flashSale = $this->flashSaleRepository->query()->where('type', 'flash_sale')->find($flashSale);
        if (!$flashSale) {
            return response()->json(['message' => 'Flash Sale not found'], 404);
        }
        $flashSale = $this->flashSaleRepository->update($request->validated(), $flashSale->id);
        return response()->json(['data' => new FlashSaleResource($flashSale)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($flashSale): JsonResponse
    {
        $flashSale = $this->flashSaleRepository->query()->where('type', 'flash_sale')->find($flashSale);
        if (!$flashSale) {
            return response()->json(['message' => 'Flash Sale not found'], 404);
        }
        $this->flashSaleRepository->delete($flashSale->id);
        return response()->json(['data' => true]);
    }
}
