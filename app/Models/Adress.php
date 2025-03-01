<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adress extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'first_name',
        'last_name',
        'address',
        'street_name',
        'street_number',
        'lat',
        'lon',
        'address2',
        'country',
        'city',
        'zip',
        'phone',
        'phone_country_code',
        'is_primary',
        'user_id',
    ];
    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
