<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = ["txt_refno", "response_code"];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_payments');
    }
}
