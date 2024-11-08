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
        Schema::create('bespoke_customization_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bespoke_customization_id');
            $table->foreign('bespoke_customization_id')->references('id')->on('bespoke_customizations')->onDelete('cascade');
            $table->string("name");
            $table->float("price");
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
        Schema::dropIfExists('bespoke_customization_types');
    }
};
