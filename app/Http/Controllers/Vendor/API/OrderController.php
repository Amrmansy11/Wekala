<?php

namespace App\Http\Controllers\Vendor\API;

use Exception;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\Vendor\OrderRepository;
use App\Repositories\Vendor\ProductRepository;

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
        $orders = $this->orderRepository->myBuyerOrders($perPage);
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

    public function changeStatus($id): JsonResponse
    {
        $order = $this->orderRepository->query()
            ->where('seller_vendor_id', AppHelper::getVendorId())
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $currentStatus = $order->status;

        $nextStatus = match ($currentStatus) {
            'pending'   => 'confirmed',
            'confirmed' => 'shipped',
            'shipped'   => 'completed',
            default     => null,
        };

        if (! $nextStatus) {
            return response()->json(['message' => 'Cannot change status further'], 400);
        }

        $this->orderRepository->update(['status' => $nextStatus], $order->id);
        $order->refresh();
        // if ($order->status === 'completed') {
        //     foreach ($order->items as $item) {
        //         $originalProduct = $item->product;
        //         if ($originalProduct) {
        //             $product = $this->productRepository->store([
        //                 'name' => $originalProduct->name,
        //                 'description' => $originalProduct->description,
        //                 'vendor_id' => $order->buyer_vendor_id,
        //                 'parent_id' => $order->seller_vendor_id,
        //                 'material_id' => $originalProduct->material_id,
        //                 'barcode' => uniqid(),
        //                 'wholesale_price' => $originalProduct->wholesale_price,
        //                 'consumer_price' => $originalProduct->consumer_price,
        //                 'creatable_type' => get_class($originalProduct->creatable),
        //                 'creatable_id' => $originalProduct->creatable_id,
        //                 'category_id' => $originalProduct->category_id,
        //                 'sub_category_id' => $originalProduct->sub_category_id,
        //                 'sub_sub_category_id' => $originalProduct->sub_sub_category_id,
        //                 'brand_id' => $originalProduct->brand_id,
        //                 'size_chart_id' => $originalProduct->size_chart_id,
        //                 'stock' => 0,
        //                 'publish_type' => now(),
        //                 'publish_date' => now(),
        //                 'elwekala_policy' => $originalProduct->elwekala_policy
        //             ]);

        //             // Copy media
        //             foreach ($originalProduct->getMedia('images') as $media) {
        //                 $path = $media->getPath();
        //                 if (file_exists($path)) {
        //                     $product->addMedia($path)
        //                         ->usingFileName($media->file_name)
        //                         ->toMediaCollection('images');
        //                 }
        //             }

        //             if ($item->product_variant_id) {
        //                 $originalVariant = $originalProduct->variants()->find($item->product_variant_id);

        //                 if ($originalVariant) {
        //                     $newVariant = $product->variants()->create([
        //                         'color' => $originalVariant->color,
        //                         'bags' => $item->quantity,
        //                         'total_pieces' => $originalVariant->total_pieces,
        //                     ]);

        //                     foreach ($originalVariant->getMedia('variant_images') as $vMedia) {
        //                         $path = $vMedia->getPath();
        //                         if (file_exists($path)) {
        //                             $newVariant->addMedia($path)
        //                                 ->usingFileName($vMedia->file_name)
        //                                 ->toMediaCollection('images');
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        return response()->json([
            'data'    => new OrderResource($order->fresh()),
            'message' => 'Status updated successfully',
        ]);
    }
    public function cancelOrder(int $orderId): JsonResponse
    {
        $order = $this->orderRepository->query()
            ->where('seller_vendor_id', AppHelper::getVendorId())
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
