<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\ProductStatusException;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'status'];
    protected $appends = ["threeStar"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function one_image()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->limit(1);
    }

    public function variation()
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'id');
    }
    public function subCategories()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'id');
    }
    public function orders()
    {
        return $this->hasMany(OrderProduct::class, 'product_id', 'id');
    }
    public function history()
    {
        return $this->hasMany(UserProductHistory::class, 'product_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany(LikeProduct::class, 'product_id', 'id');
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id');
    }

    // check if product is a ring
    public function scopeIsRing(Builder $query, $id)
    {
        return $query->where('sub_category_id', 1)->where('id', $id);
    }




    public function scopeIsAllRing(Builder $query)
    {
        return $query->where('sub_category_id', 1);
    }

    public function scopeIsBrac(Builder $query, $id)
    {
        return $query->where('sub_category_id', 2)->where('id', $id);
    }




    public function scopeIsAllBrac(Builder $query)
    {
        return $query->where('sub_category_id', 2);
    }
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            $builder->orderBy('id', 'DESC')
                ->where('status', 'approved')
                ->where('is_delete', false)
                ->where('is_active', true);
            // if ($builder->count() === 0) {
            //     throw new ProductStatusException("Product is not active or product is deleted.");
            // }
        });
    }
    public function product_enum()
    {
        return $this->hasOne(ProductEnum::class, 'product_id', 'id');
    }
    protected function getThreeStarAttribute()
    {
        $three_star = 0;
        $three_star = ProductReview::where('product_id', $this->id)->where('stars', '>=', '3')->count();
        return $three_star;
    }
}
