<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'product_id'    => $this->product_id,
            'title'         => $this->products->title,
            'description'   => $this->products->desc,
            'price'         => $this->product_price,
            'quantity'      => $this->qty,
            'subtotal'      => $this->subtotal,
            'variation'     => $this->variation ? [
                'id'   => $this->variation->id,
                'size' => $this->variation->size,
                'price' => $this->variation->price
            ] : null,
            'product_images' => $this->product_images ?? [],
        ];
    }
}
