<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BespokeCustomization extends Model
{
    use HasFactory;
    protected $fillable = ["name", "image"];

    public function bsp_type()
    {
        return $this->hasMany(BespokeCustomizationType::class, "bespoke_customization_id", "id");
    }
}
