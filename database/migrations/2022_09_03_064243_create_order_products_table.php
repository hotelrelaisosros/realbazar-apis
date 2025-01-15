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
            $table->unsignedBigInteger('variant_id')->nullable();

            $table->string('subtotal')->nullable();
            $table->string('discount')->nullable();
            $table->string('qty')->nullable();
            $table->string('size')->nullable();
            $table->float('product_price')->default(0.00);
            $table->float('customization_price')->default(0.00);

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('variant_id')->references('id')->on('product_variations');

            $table->unsignedBigInteger('metal_type_id')->nullable();
            $table->string('faceting_id')->nullable();

            $table->unsignedBigInteger('gem_shape_id')->nullable();
            $table->unsignedBigInteger('band_width_id')->nullable();
            $table->unsignedBigInteger('accent_stone_type_id')->nullable();
            $table->unsignedBigInteger('setting_height_id')->nullable();
            $table->unsignedBigInteger('prong_style_id')->nullable();
            $table->unsignedBigInteger('ring_size_id')->nullable();
            $table->string('bespoke_customization_types_id')->nullable();
            $table->string('birth_stone_id')->nullable();
            $table->unsignedBigInteger('gem_stone_id')->nullable();
            $table->unsignedBigInteger('gem_stone_color_id')->nullable();


            // Engraved text column
            $table->string('engraved_text')->nullable();
            $table->string('metal_type_karat')->nullable();


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
