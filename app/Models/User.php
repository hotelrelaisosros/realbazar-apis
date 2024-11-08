<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function cnic_image()
    {
        return $this->hasMany(CnicImage::class, 'user_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id', 'id');
    }

    public function follow()
    {
        return $this->hasMany(FollowUserShop::class, 'shop_id', 'id');
    }

    public function userPackagePayment()
    {
        return $this->hasMany(PackagePayment::class, 'user_id', 'id');
    }

    public function users_orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function sellers_orders()
    {
        return $this->hasMany(Order::class, 'seller_id', 'id');
    }

    public function sellers_orders_products()
    {
        return $this->hasManyThrough(OrderProduct::class, Order::class, 'seller_id');
    }
}
