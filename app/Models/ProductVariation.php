<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ImageHelper;

use App\Exceptions\ProductStatusException;


class ProductVariation extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'price', 'title', 'stock'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        // Adding the global scope for active products
        static::addGlobalScope('activeProduct', function ($builder) {
            $builder->whereHas('product', function ($query) {
                $query->where('status', 'approved')
                    ->where('is_delete', false)
                    ->where('is_active', true);
            });
            // if ($builder->count() === 0) {
            //     throw new ProductStatusException("Product is not active or product is deleted.");
            // }
        });
    }
    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }


    public function metal_type()
    {
        return $this->belongsTo(MetalTypeCategory::class, 'metal_type_id', 'id')
            ->select(['id', 'title', 'image']);
    }


    public function gem_shape()
    {
        return $this->belongsTo(Gemshape::class, 'gem_shape_id', 'id');
    }
}
