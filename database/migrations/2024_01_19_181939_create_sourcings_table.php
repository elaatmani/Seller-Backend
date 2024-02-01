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
        Schema::create('sourcings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->text('product_name')->nullable();
            $table->longText('product_url')->nullable();
            $table->integer('estimated_quantity')->nullable();
            $table->text('destination_country')->nullable();
            $table->text('note_by_seller')->nullable();
            $table->text('note_by_admin')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('quotation_status')->default(config('status.sourcings.quotation_status.default')['value']);
            $table->string('sourcing_status')->default(config('status.sourcings.sourcing_status.default')['value']);
            $table->float('cost_per_unit')->default(0);
            $table->float('total_cost')->default(0);
            $table->float('additional_fees')->default(0);
            $table->integer('processing_days')->nullable()->default(0);
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
        Schema::dropIfExists('sourcings');
    }
};
