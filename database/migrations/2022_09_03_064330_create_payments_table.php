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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // $table->string('stripe_id')->nullable();
            $table->string('total')->nullable();
            // $table->string('brand')->nullable();
            $table->string('txt_refno')->nullable();
            $table->string('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->enum('payment_method', ['easypaisa', 'jazzcash', 'cash on delivery']);
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
        Schema::dropIfExists('payments');
    }
};
