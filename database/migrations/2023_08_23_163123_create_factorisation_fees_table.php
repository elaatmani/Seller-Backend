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
        Schema::create('factorisation_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factorisation_id');
            $table->string('feename');
            $table->integer('feeprice');
            $table->timestamps();

            $table->foreign('factorisation_id')->references('id')->on('factorisations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factorisation_fees');
    }
};
