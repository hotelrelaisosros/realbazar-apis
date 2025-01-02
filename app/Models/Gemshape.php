<?php

namespace App\Models;

use App\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gemshape extends Model
{
    use HasFactory;

    protected $fillable = ["name", "image"];

    public function getImageAttribute($value)
    {
        return ImageHelper::formatImageUrl($value);
    }
}
