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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ref')->unique();
            $table->string('name');
            $table->float('selling_price');
            $table->float('buying_price');
            $table->string('link_video')->nullable();
            $table->string('link_store')->nullable();
            $table->string('transport_mode')->nullable();
            $table->dateTime('expedition_date')->nullable();
            $table->string('country_of_purchase')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('products');
    }
};
