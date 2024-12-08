<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'price', 'title', 'stock'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
