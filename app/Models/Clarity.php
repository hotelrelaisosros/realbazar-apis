<?php

namespace App\Models;

use App\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clarity extends Model
{
    use HasFactory;
    protected $table = 'clarities';
    protected $fillable = ['name', 'price', 'variant_id', 'image'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getImageAttribute($value)
    {
        return ImageHelper::formatImageUrl($value);
    }
}
