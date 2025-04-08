<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetalKerat extends Model
{
    use HasFactory;
    protected $table = 'metal_kerate'; // Explicitly define the table name

    protected $fillable = ['kerate', 'price', 'variant_id', 'stone_type'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
