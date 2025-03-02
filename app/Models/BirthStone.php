<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthStone extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = ["name", "price", "image"];
}
