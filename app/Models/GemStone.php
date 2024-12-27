<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GemStone extends Model
{
    use HasFactory;

    protected $table = 'gem_stones';
    protected $fillable = [
        'type',
        'carat',
        'shape',
        'dimension',
        'faceting',
        'price',
        'gemstone_color_id',
        'color',
        'clarity',
    ];
}
