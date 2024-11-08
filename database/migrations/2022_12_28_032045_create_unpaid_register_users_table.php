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
        Schema::create('unpaid_register_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_address')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('shop_number')->nullable();
            $table->string('market_name')->nullable();
            $table->string('cnic_number')->nullable();
            $table->string('price')->nullable();
            $table->string('txt_refno')->nullable();
            $table->string('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->enum('payment_method',['easypaisa','jazzcash']);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unpaid_register_users');
    }
};
