<?php

namespace App\Http\Resources;

use App\Models\Gemshape;
use App\Models\MetalTypeCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationAdminResource extends JsonResource
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
            "metal_type" => $this->metal_type_id && MetalTypeCategory::find($this->metal_type_id) ? MetalTypeCategory::find($this->metal_type_id) : null,
            "gem_shape" => $this->gem_shape_id && Gemshape::find($this->gem_shape_id) ? Gemshape::find($this->gem_shape_id) : null,
        ];
    }
}
