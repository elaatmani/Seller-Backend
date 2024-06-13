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
        Schema::table('user_product', function (Blueprint $table) {
            $table->dropForeign(['role_id']); // Add this line
            $table->dropColumn('role_id');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_product', function (Blueprint $table) {
            $table->dropColumn('product_id');
            $table->dropColumn('type');
            $table->unsignedBigInteger('role_id'); // Add this line
            $table->foreign('role_id')->references('id')->on('roles'); // Add this line
        });
    }
};
