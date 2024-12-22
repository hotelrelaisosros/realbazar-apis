<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'image' => ImageHelper::formatImageUrl($this->image),
            'category' => $this->categories->first() ? [
                'id' => $this->categories->first()->id,
                'name' => $this->categories->first()->name,
                'url' => $this->categories->first()->url,
                'image' =>  ImageHelper::formatImageUrl($this->categories->first()->image),
            ] : null,
        ];
    }
}
