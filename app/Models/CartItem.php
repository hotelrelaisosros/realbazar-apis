<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'name',
        'price',
        'initial_price',
        'quantity',
        'attributes',
        'customizables',
        'variant_id',
        'cart_id',
    ];
}
