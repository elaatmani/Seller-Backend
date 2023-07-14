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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->string('password');
            $table->tinyInteger('is_online')->default(0);
            $table->tinyInteger('having_all')->default(0);
            $table->integer('city')->nullable();
            $table->tinyInteger('status');
            $table->timestamp('last_action')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
