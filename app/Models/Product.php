<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function ($builder) {
            $builder->orderBy('id', 'DESC')
                ->where('status', 'approved')
                ->where('is_delete', false)
                ->where('is_active', true);
        });
        // if (auth()->check()) {
        //     $user = auth()->user()->load('role');
        //     if ($user->role->name == 'user') {
        //         static::addGlobalScope('active', function ($builder) {
        //             $builder->orderBy('id', 'DESC')->where('status', 'approved')
        //                 ->where('is_delete', false)
        //                 ->where('is_active', true)
        //                 ->whereRelation('user', 'is_block', false)
        //                 ->whereRelation('user', 'is_active', true);
        //         });
        //     } elseif ($user->role->name == 'admin') {
        //         static::addGlobalScope('active', function ($builder) {
        //             $builder->orderBy('id', 'DESC')->whereRelation('user', 'is_block', false);
        //         });
        //     } else {
        //         static::addGlobalScope('active', function ($builder) {
        //             $builder->orderBy('id', 'DESC')->whereRelation('user', 'is_block', false)->where('is_delete', false);
        //         });
        //     }
        // } else {
        //     static::addGlobalScope('active', function ($builder) {
        //         $builder->orderBy('id', 'DESC')->where('status', 'approved')
        //             ->where('is_delete', false)
        //             ->where('is_active', true)
        //             ->whereRelation('user', 'is_block', false)
        //             ->whereRelation('user', 'is_active', true);
        //     });
        // }
    }
    public function productEnum()
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
