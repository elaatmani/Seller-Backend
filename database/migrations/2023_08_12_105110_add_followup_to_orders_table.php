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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('followup_id')->nullable()->default(null);
            $table->foreign('followup_id')->references('id')->on('users')->nullOnDelete();
            $table->string('followup_confirmation')->nullable()->default(null);
            $table->date('followup_reported_date')->nullable()->default(null);
            $table->text('followup_reported_note')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('followup_id');
            $table->dropColumn('followup_confirmation');
            $table->dropColumn('followup_reported_date');
            $table->dropColumn('followup_reported_note');
        });
    }
};
