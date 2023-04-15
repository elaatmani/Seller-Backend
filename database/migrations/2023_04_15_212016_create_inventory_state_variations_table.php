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
        Schema::create('inventory_state_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_state_id');
            $table->string('size');
            $table->string('color');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('inventory_state_id')->references('id')->on('inventory_states')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_state_variations');
    }
};
