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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('subtotal')->nullable();
            $table->string('discount')->nullable();
            $table->string('qty')->nullable();
            $table->string('size')->nullable();
            $table->float('product_price')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');

            $table->string('text_engraving')->nullable();

            $table->string('metal_karat')->nullable();
            $table->string('band_width')->nullable();
            $table->string('setting_height')->nullable();
            $table->string('ring_size')->nullable();
            $table->string('prong_style')->nullable();

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
        Schema::dropIfExists('order_products');
    }
};
