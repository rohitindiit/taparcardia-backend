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
        Schema::create('user_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to the users table
            $table->enum('address_type', ['billing', 'shipping']); // Address type: billing or shipping
            $table->string('address_line1'); // First line of the address
            $table->string('address_line2')->nullable(); // Second line of the address (optional)
            $table->string('city'); // City
            $table->string('state'); // State
            $table->string('postal_code'); // Postal code
            $table->string('country'); // Country
            $table->string('phone')->nullable(); // Contact phone number (optional)
           $table->string('is_default')->default('0');
         

            // Foreign key constraint
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
        Schema::dropIfExists('user_address');
    }
};
