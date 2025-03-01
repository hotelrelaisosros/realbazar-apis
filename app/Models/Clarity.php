<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clarity extends Model
{
    use HasFactory;
    protected $table = 'clarities';
    protected $fillable = ['clarity', 'price', 'variant_id'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
