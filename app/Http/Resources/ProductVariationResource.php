<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "title" => $this->title,
            "size" => $this->size,
            "stock" => $this->stock,
            "price" => $this->price,
            "metal_type_id" => $this->metal_type_id,
            "gem_shape_id" => $this->gem_shape_id,
            // 'product_images' => ProductImageResource::collection($this->whenLoaded('product_images')),

        ];
    }
}
