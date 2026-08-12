<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'price' => (float) $this->price,
            'is_available' => $this->is_available,
            'preparation_time' => $this->preparation_time,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'addons' => ProductAddonResource::collection($this->whenLoaded('addons')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
