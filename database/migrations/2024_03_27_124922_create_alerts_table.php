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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('created_by')->nullable();
            $table->longText('content')->nullable();
            $table->enum('type', ['global', 'user', 'role']);
            $table->boolean('is_active')->default(true);
            $table->enum('variant', ['success', 'info', 'danger', 'warning'])->default('info');
            $table->string('target')->nullable();
            $table->boolean('closeable')->default(true);
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
        Schema::dropIfExists('alerts');
    }
};
