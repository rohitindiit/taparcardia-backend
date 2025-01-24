<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
      protected $fillable = [
        'user_id',
        'vendor_id',
        'total_price' ,
        'order_status' ,  
        'payment_status' , 
        'shipping_address',
        'billing_address' ,
        'payment_method'
       
    ];
     protected $table = 'orders';
}
