<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id(); // Primary key
        $table->unsignedBigInteger('user_id'); // Define user_id column
        $table->unsignedBigInteger('vendor_id'); // Define vendor_id column
        $table->decimal('total_price', 10, 2); // Use decimal for price
        $table->string('order_status'); // Corrected typo: 'order_status'
        $table->string('payment_status'); 
        $table->text('shipping_address');
        $table->text('billing_address');
        $table->string('payment_method');
        
        // Foreign keys
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
