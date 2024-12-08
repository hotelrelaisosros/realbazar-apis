<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEnum extends Model
{
    use HasFactory;
    protected $fillable = [
        'metal_types',
        'gem_shape_id',
        'default_metal_id',
        'band_width_ids',
        'accent_stone_type_ids',
        'setting_height_ids',
        'prong_style_ids',
        'ring_size_ids',
        'bespoke_customization_ids',
        'birth_stone_ids',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
