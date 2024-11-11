<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GemStoneColor extends Model
{
    use HasFactory;

    protected $fillable = ["name", "image"];
}
