<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

     protected $fillable = [
        'product_name',
        'number_of_coins',
        'price',
        'vendor_id',
        'product_image',
    ];

     public function carts()
    {
        return $this->hasMany(UserCart::class, 'product_id');
    }
}

