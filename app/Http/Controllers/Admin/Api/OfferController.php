<?php
namespace App\Http\Controllers\Admin\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Offer\OfferStoreRequest;
use App\Http\Resources\OfferResource;
use App\Repositories\Vendor\OfferRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends AdminController
{
    protected OfferRepository $offerRepository;

    public function __construct(OfferRepository $offerRepository)
    {
//         $this->middleware('permission:vendor_offers_view')->only('index');
//        $this->middleware('permission:vendor_offers_create')->only('store');
//        $this->middleware('permission:vendor_offers_update')->only('update');
//        $this->middleware('permission:vendor_offers_delete')->only('destroy');
//
        $this->offerRepository = $offerRepository;
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status');
        $type = $request->get('type');
        $name = $request->get('name');

        $query = $this->offerRepository->query()
            ->with('products')
            ->when($status, function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('start', '<=', now())
                        ->where('end', '>=', now());
                } elseif ($status === 'expired') {
                    $q->where('end', '<', now());
                }
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })->when($name, function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });

        $offers = $query->paginate($perPage);


        return response()->json([
            'data' => OfferResource::collection($offers),
            'pagination' => [
                'currentPage' => $offers->currentPage(),
                'total' => $offers->total(),
                'perPage' => $offers->perPage(),
                'lastPage' => $offers->lastPage(),
                'hasMorePages' => $offers->hasMorePages(),
            ]
        ]);
    }

    public function store(OfferStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $offer = $this->offerRepository->store($data, $request->file('logo'), $request->file('cover'));        return response()->json(['message' => 'Offer created successfully'], 201);
    }

    public function show($id): JsonResponse
    {
        $offer = $this->offerRepository->show($id);
        return response()->json(['data' => new OfferResource($offer)]);
    }

    public function update(OfferStoreRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $offer = $this->offerRepository->update($id, $data, $request->file('logo'), $request->file('cover'));        return response()->json(['message' => 'Offer updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $this->offerRepository->delete($id);
        return response()->json(['message' => 'Offer deleted successfully']);
    }
}
