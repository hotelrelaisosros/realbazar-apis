<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductNonRingResource extends JsonResource
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
            'id' => $this->id,
            'sub_category_id' => $this->sub_category_id,
            'title' => $this->title,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'color' => $this->color,
            'desc' => $this->desc,
            'tags' => $this->tags,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_delete' => $this->is_delete,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'is_new_arrival' => $this->is_new_arrival,
            'image' => $this->image,
            'user_id' => $this->user_id,
            'configurations' => $this->configurations,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'threeStar' => $this->threeStar,
            // 'variation' => ProductVariationResource::collection(),
            // 'images' => ProductImageResource::collection($this->product_images),
        ];
    }
}
