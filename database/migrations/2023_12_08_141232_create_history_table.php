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
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            // Which table are we tracking
            $table->string('trackable_type');
            // Which record from the table are we referencing
            $table->integer('trackable_id')->unsigned();
            // Who made the action
            $table->integer('actor_id')->unsigned();
            // What did they do
            $table->string('body')->nullable();
            // field
            $table->json('fields')->nullable();

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
        Schema::dropIfExists('history');
    }
};
