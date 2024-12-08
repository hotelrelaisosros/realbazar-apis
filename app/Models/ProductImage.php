<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\ProductVariation;

class ProductImage extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variant_id', 'id');
    }
}
