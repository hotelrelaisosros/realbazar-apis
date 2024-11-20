<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BespokeCustomizationType extends Model
{
    use HasFactory;

    protected $fillable  = ["name", "bespoke_customization_id", "price"];

    public function bsp()
    {
        return $this->belongsTo(BespokeCustomization::class, "bespoke_customization_id", "id");
    }
}
