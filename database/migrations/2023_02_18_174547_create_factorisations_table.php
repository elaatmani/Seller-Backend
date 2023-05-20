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
        Schema::create('factorisations', function (Blueprint $table) {
            $table->id();
            $table->string('facturation_number')->unique();
            $table->unsignedBigInteger('delivery_id');
            $table->boolean('close');
            $table->boolean('paid');
            $table->integer('commands_number');
            $table->integer('price');
            $table->dateTime('close_at');
            $table->dateTime('paid_at');
            $table->string('comment');
            $table->timestamps();


            $table->foreign('delivery_id')->on('users')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factorisations');
    }
};
