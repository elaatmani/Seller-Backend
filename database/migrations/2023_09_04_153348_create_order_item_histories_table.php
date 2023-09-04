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
        Schema::create('order_item_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('item_id');
            $table->unsignedBigInteger('user_id');
            $table->string('product');
            $table->string('price');
            $table->string('quantity');


           
            $table->timestamps();


            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_histories');
    }
};
