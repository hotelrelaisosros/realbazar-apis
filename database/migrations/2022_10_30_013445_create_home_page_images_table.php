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
        Schema::create('home_page_images', function (Blueprint $table) {
            $table->id();
            // $table->text('title')->nullable();
            $table->mediumText('url')->nullable();
            $table->text('image')->nullable();
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->boolean('is_top_rating')->default(false);
            $table->boolean('is_just_for_you')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_retailer')->default(false);
            $table->boolean('is_wholesaler')->default(false);
            $table->boolean('is_app')->default(false);
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
        Schema::dropIfExists('home_page_images');
    }
};
