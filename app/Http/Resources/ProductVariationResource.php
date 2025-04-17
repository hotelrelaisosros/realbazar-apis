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


            "total_price" => ($this->product ? $this->product->price : 0) + $this->price,
            "total_discounted_price" => ($this->product ? $this->product->price : 0) + $this->price - ($this->product ? $this->product->discount_price : 0),
            "images" => $this->product_images && isset($this->product_images[0])
                ? new ProductImageResource($this->product_images[0])
                : null,
        ];
    }
}
