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
        Schema::create('adresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_primary')->default(false); // To flag the default address.
            $table->foreign('user_id')->references('id')->on('users');
            $table->string("surname");
            $table->string("first_name");
            $table->string("last_name");
            $table->string("address");
            $table->string("street_name");
            $table->string("street_number");

            $table->string("lat");
            $table->string("lon");

            $table->string("address2");
            $table->string("country");

            $table->string("city");
            $table->string("zip");
            $table->string('phone', 20)->nullable();
            $table->string('phone_country_code', 5)->nullable();
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
        Schema::dropIfExists('adresses');
    }
};
