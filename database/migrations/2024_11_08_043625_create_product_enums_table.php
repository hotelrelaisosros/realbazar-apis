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
        Schema::create('product_enums', function (Blueprint $table) {
            $table->id();
            $table->json('metal_types')->nullable();
            $table->integer('gem_shape_id');
            $table->integer('default_metal_id');

            $table->string('band_width_ids');
            $table->string("accent_stone_type_ids");
            $table->string('setting_height_ids'); // setting height optional
            $table->string('prong_style_ids');
            $table->string('ring_size_ids');

            $table->string('bespoke_customization_ids');
            $table->string('birth_stone_ids');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
        Schema::dropIfExists('product_enums');
    }
};
