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
            $table->id();
            $table->string('fullname');
            $table->string('product_name');
            $table->string('agente_id')->nullable();
            $table->string('upsell')->nullable();
            $table->string('phone');
            $table->string('city');
            $table->string('adresse');
            $table->integer('quantity');
            $table->string('confirmation')->nullable();
            $table->integer('affectation')->nullable();
            $table->string('delivery')->nullable();
            $table->string('note');
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
