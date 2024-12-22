<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
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
            // 'id' => $this->id,
            'name' => $this->name,
            'image' => ImageHelper::formatImageUrl($this->image),
            'image_collection' => ImageHelper::formatImageCollection($this->image_collection),
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
        ];
    }
}
