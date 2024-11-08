<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompleteDemandProduct extends Model
{
    use HasFactory;

    public function shop()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function demand_product()
    {
        return $this->belongsTo(DemandProduct::class, 'demand_product_id', 'id');
    }
}
