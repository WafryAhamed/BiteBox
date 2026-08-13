<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'order_number' => $this->order_number,
            'order_type' => $this->order_type instanceof \BackedEnum ? $this->order_type->value : $this->order_type,
            'payment_method' => $this->payment_method instanceof \BackedEnum ? $this->payment_method->value : $this->payment_method,
            'payment_status' => $this->payment_status instanceof \BackedEnum ? $this->payment_status->value : $this->payment_status,
            'order_status' => $this->order_status instanceof \BackedEnum ? $this->order_status->value : $this->order_status,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'special_instruction' => $this->special_instruction,
            'user' => new UserResource($this->whenLoaded('user')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
