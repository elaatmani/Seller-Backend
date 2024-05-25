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
        Schema::create('external_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('actor_id')->nullable();
            $table->string('url');
            $table->string('method');
            $table->text('headers')->nullable();
            $table->longText('body')->nullable();
            $table->integer('response_code');
            $table->longText('response_body')->nullable();

            $table->timestamps(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_requests');
    }
};