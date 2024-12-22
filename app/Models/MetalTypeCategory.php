<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ImageHelper;

class MetalTypeCategory extends Model
{
    use HasFactory;
    protected $fillable = ["title", "image", "type"];

    public function getImageAttribute($value)
    {
        return ImageHelper::formatImageUrl($value);
    }
}
