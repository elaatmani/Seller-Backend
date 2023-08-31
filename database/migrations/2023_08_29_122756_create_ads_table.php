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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->unsignedBigInteger('product_id');
            $table->float('amount');
            $table->date('ads_at');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products');
            // $table->foreign('source')->references('source')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads');
    }
};

