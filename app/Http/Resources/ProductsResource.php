<?php

namespace App\Http\Resources;

use App\Models\ProductReview;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\Framework\Constraint\Count;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return [
        //     'id' => $this->id,
        //     'title' => $this->title,
        //     'tags' => preg_replace('/[^A-Za-z0-9\,\" "]/', "", json_decode($this->tags)),
        //     'price' => $this->price,
        //     'discount' => $this->discount_price,
        //     'product_description' => $this->desc,
        //     'product_status' => $this->status,
        //     'color' => $this->color,
        //     'is_delete' => $this->is_delete,
        //     'is_active' => $this->is_active,
        //     'is_featured' => $this->is_featured,
        //     'is_new_arrival' => $this->is_new_arrival,
        //     'is_trending' => $this->is_trending,
        //     'variation' => $this->variation,
        //     'image' => $this->images,
        //     'shop' => $this->user,
        //     'category' => $this->subCategories->categories,
        //     'sub_category' => $this->subCategories,
        //     'followers' => $this->user->follow->sortByDesc('id')->values(),
        //     'likes' => $this->likes->sortByDesc('id')->values(),
        //     'reviews' => $this->reviews->sortByDesc('id')->values(),
        //     'rating' => round($this->reviews->avg('stars'), 1),
        //     'totalReviews' => $this->reviews->count(),
        //     'positiveReviews' => ($this->threeStar != 0) ? ($this->threeStar / $this->reviews->count()) * (100) : 0,
        //     'totalLikes' => $this->likes->count(),
        //     'totalFollowers' => $this->user->follow->count(),
        // ];
    }
}
