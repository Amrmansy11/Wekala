<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Voucher\VoucherStoreRequest;
use App\Http\Resources\VoucherResource;
use App\Repositories\Vendor\VoucherRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends VendorController
{
    protected VoucherRepository $voucherRepository;

    public function __construct(VoucherRepository $voucherRepository)
    {
        // $this->middleware('permission:vendor_vouchers_view')->only('index');
        // $this->middleware('permission:vendor_vouchers_create')->only('store');
        // $this->middleware('permission:vendor_vouchers_update')->only('update');
        // $this->middleware('permission:vendor_vouchers_delete')->only('destroy');
        $this->voucherRepository = $voucherRepository;
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $name = $request->get('name');
        $status = $request->get('status');

        $query = $this->voucherRepository->query()
            ->where('vendor_id', AppHelper::getVendorId())
            ->with('products')
            ->when($name, function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            })
            ->when($status, function ($q) use ($status) {
                $now = now();
                if ($status === 'active') {
                    $q->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now);
                } elseif ($status === 'expired') {
                    $q->where('end_date', '<', $now);
                }
            });
        $vouchers = $query->paginate($perPage);

        return response()->json([
            'data' => VoucherResource::collection($vouchers),
            'pagination' => [
                'currentPage' => $vouchers->currentPage(),
                'total' => $vouchers->total(),
                'perPage' => $vouchers->perPage(),
                'lastPage' => $vouchers->lastPage(),
                'hasMorePages' => $vouchers->hasMorePages(),
            ]
        ]);
    }

    /**
     * @param VoucherStoreRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(VoucherStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $this->voucherRepository->store($data);
        return response()->json(['message' => 'Voucher created successfully'], 201);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $voucher = $this->voucherRepository->show($id);
        return response()->json(['data' => new VoucherResource($voucher)]);
    }

    /**
     * @param VoucherStoreRequest $request
     * @param $id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(VoucherStoreRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $this->voucherRepository->update($id, $data);
        return response()->json(['message' => 'Voucher updated successfully']);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $this->voucherRepository->delete($id);
        return response()->json(['message' => 'Voucher deleted successfully']);
    }
}
