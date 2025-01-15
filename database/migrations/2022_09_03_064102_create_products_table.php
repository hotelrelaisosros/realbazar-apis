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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('title')->nullable();
            $table->float('price')->default(0.00);
            $table->float('discount_price')->default(0.00)->nullable();
            $table->string('color')->nullable();
            $table->mediumText('desc')->nullable();
            $table->string('tags')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_new_arrival')->default(true);
            // $table->string('size')->nullable();
            // $table->string('brand')->nullable();
            // $table->string('selected_qty')->nullable();
            // $table->string('type')->nullable();
            // $table->string('variations')->nullable();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            //ring type
            $table->string('image')->nullable();
            // $table->integer('product_enum_id')->default(0);
            // $table->integer("shape_id")->nullable();
            // $table->integer("metal_type_id")->nullable(); //is conneced to Product_images_table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories');
            $table->json('configurations')->nullable();

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
        Schema::dropIfExists('products');
    }
};
