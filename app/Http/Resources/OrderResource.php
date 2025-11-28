<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => sprintf('#%010d', $this->id),
            'date' => $this->created_at->format('d M, Y'),
            'status' => $this->status,
            'grand_total' => $this->total + $this->children->sum('total'),
            'sellers_count' => 1 + $this->children->count(),
            'total_items' => $this->items->count() + $this->children->sum(fn($child) => $child->items->count()),
            'sellers' => $this->formatSellers(),
        ];
    }

    /**
     * Format sellers data (parent + children)
     */
    private function formatSellers(): array
    {
        return collect([$this->resource])
            ->merge($this->children)
            ->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'seller_name' => $order->sellerVendor->store_name ?? 'N/A',
                    // 'seller_logo' => $order->sellerVendor->getFirstMediaUrl('vendor_logo') ?? null,
                    'items_count' => $order->items->count(),
                    'subtotal' => (float) $order->total,
                    'discount' => (float) $order->discount,
                    'delivery' => (float) $order->delivery,
                    'status' => $order->status,
                    'items' => OrderItemResource::collection($order->items),
                ];
            })
            ->toArray();
    }
}
