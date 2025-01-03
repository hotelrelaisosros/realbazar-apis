<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->url,
            'image' => ImageHelper::formatImageUrl($this->image),
            'sub_category' => $this->subCategory->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'url' => $sub->url,
                    'image' => ImageHelper::formatImageUrl($sub->image),
                ];
            }),
        ];
    }
}
