<?php

namespace App\Http\Controllers\Consumer\API;

use App\Models\User;
use Exception;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\Consumer\OrderRepository;
use App\Repositories\Vendor\ProductRepository;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private readonly OrderRepository $orderRepository, private readonly ProductRepository $productRepository) {}

    /**
     * GET /vendor/orders (my orders)
     */
    public function getSellerOrders(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->string('status', null);
        $orders = $this->orderRepository->mySellerOrders($perPage, $status);
        return response()->json([
            'data' => OrderResource::collection($orders),
            'pagination' => [
                'currentPage' => $orders->currentPage(),
                'total' => $orders->total(),
                'perPage' => $orders->perPage(),
                'lastPage' => $orders->lastPage(),
                'hasMorePages' => $orders->hasMorePages(),
            ]
        ]);
    }
    public function getBuyerOrders(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $orders = $this->orderRepository->myBuyerOrdersGroupedByVendorForConsumer($perPage);

        return response()->json([
            'data' => $orders->getCollection(), // فقط الأوردرات بعد التحويل
            'pagination' => [
                'currentPage' => $orders->currentPage(),
                'total' => $orders->total(),
                'perPage' => $orders->perPage(),
                'lastPage' => $orders->lastPage(),
                'hasMorePages' => $orders->hasMorePages(),
            ]
        ]);
    }


    public function cancelOrder(int $orderId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $order = $this->orderRepository->query()
            ->where('user_id', $user->id)
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (in_array($order->status, ['shipped', 'delivered', 'completed', 'cancelled'])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Order cancelled successfully']);
    }




    /**
     * POST /vendor/checkout
     * @throws Exception
     */
    public function checkout(): JsonResponse
    {
        $orders = $this->orderRepository->checkout();
        return response()->json($orders);
    }

    public function show($id): JsonResponse
    {
        $order = $this->orderRepository->show($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(['data' => new OrderResource($order)]);
    }
}
