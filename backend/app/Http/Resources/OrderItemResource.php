<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'special_instruction' => $this->special_instruction,
            'addons' => OrderItemAddonResource::collection($this->whenLoaded('addons')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
