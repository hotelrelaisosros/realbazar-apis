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
            // $table->unsignedBigInteger('product_image_id')->nullable();

            $table->unsignedBigInteger('variant_id')->nullable();

            $table->unsignedBigInteger('cart_id');

            $table->string('subtotal')->nullable();
            $table->string('discount')->nullable();
            $table->string('qty')->nullable();
            $table->string('size')->nullable();
            $table->float('product_price')->default(0.00);
            $table->float('initial_price')->default(0.00);

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('variant_id')->references('id')->on('product_variations');

            $table->unsignedBigInteger('customizables')->nullable();



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
