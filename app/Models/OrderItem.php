<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
     protected $table = 'order_items';

       protected $fillable = [
        'order_id',
        'product_id',
        'product_title' ,
        'total_price' ,  
        'qty' , 
       
       
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
