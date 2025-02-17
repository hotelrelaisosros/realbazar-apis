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
        Schema::create('demand_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demand_product_id')->nullable();
            $table->text('images')->nullable();
            $table->foreign('demand_product_id')->references('id')->on('demand_products')->onDelete('cascade');
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
        Schema::dropIfExists('demand_product_images');
    }
};
