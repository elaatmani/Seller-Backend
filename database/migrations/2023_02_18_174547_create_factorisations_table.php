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
            $table->string('factorisation_id')->unique();
            $table->unsignedBigInteger('delivery_id');
            $table->boolean('close')->default(false);
            $table->boolean('paid')->default(false);
            $table->integer('commands_number')->default(0);
            $table->float('price');
            $table->dateTime('close_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('comment')->nullable();
            $table->string('note')->nullable();
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
