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
            $table->unsignedBigInteger('user_id');
            $table->string('sheets_id')->nullable(); // generated unique id for the row
            $table->string('fullname');
            $table->unsignedBigInteger('factorisation_id')->nullable();
            $table->string('agente_id')->nullable();
            $table->string('upsell')->nullable();
            $table->string('phone');
            $table->string('city');
            $table->integer('city_id')->nullable();
            $table->string('adresse');
            $table->string('confirmation')->nullable();
            $table->integer('affectation')->nullable();
            $table->string('delivery')->nullable();
            $table->float('price');
            $table->string('note')->nullable();
            $table->string('note_d')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('cmd')->nullable();
            $table->longText('product_name')->nullable();
            $table->string('reported_agente_note')->nullable();
            $table->string('reported_delivery_note')->nullable();
            $table->date('reported_agente_date')->nullable();
            $table->date('reported_delivery_date')->nullable();
            $table->foreignId('double')->nullable();
            $table->boolean('counts_from_warehouse')->default(true);
            $table->timestamps();

            // $table->foreign('factorisation_id')->on('factorisations')->references('id');
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
        Schema::dropIfExists('orders');
    }
};
