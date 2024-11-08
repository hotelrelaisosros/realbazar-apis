<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\GemStoneFacting;

return new class extends Migration
{

    public function up()
    {
        Schema::create('gem_stones', function (Blueprint $table) {
            $table->id();
            $table->enum("type", ["M", "LGD"]);
            $table->float("carat");
            $table->string("shape")->default("Elongated Cushion");
            $table->string("dimension");
            $table->enum('faceting', array_map(fn($case) => $case->value, GemStoneFacting::cases()))
                ->default(GemStoneFacting::B->value);
            $table->float("price");
            $table->integer("gemstone_color_id");
            $table->string("color");
            $table->string("clarity")->default("VVS1");
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
        Schema::dropIfExists('gem_stones');
    }
};
