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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('user_id');
            $table->string('cus_id'); 
             $table->string('card_id'); 
              $table->string('digit'); 
               $table->string('exp_month'); 
                $table->string('exp_year'); 
                 $table->string('brand'); 
                 $table->string('payment_method_id');
                 $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('user_cards');
    }
};
