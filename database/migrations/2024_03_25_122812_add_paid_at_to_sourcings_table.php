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
        Schema::table('sourcings', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sourcings', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('paid_at');
        });
    }
};
