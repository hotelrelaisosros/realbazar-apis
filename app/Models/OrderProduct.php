<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;


    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variant_id', 'id');
    }
}
