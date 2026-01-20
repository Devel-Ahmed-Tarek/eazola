<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuOrderDetailAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_order_detail_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('term')->nullable();
            $table->string('value')->nullable();
            $table->integer('additional_price')->nullable();
            $table->foreignId('menu_order_detail_id')->constrained('menu_order_details')->cascadeOnDelete();

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
        Schema::dropIfExists('menu_order_detail_attributes');
    }
}
