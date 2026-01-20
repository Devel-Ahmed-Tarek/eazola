<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_order_id')->constrained('menu_orders')->cascadeOnDelete();
            $table->foreignId('food_menu_id')->constrained('food_menus')->cascadeOnDelete();
            $table->string('name');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('menu_tax')->nullable();
            $table->integer('image')->nullable();

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
        Schema::dropIfExists('menu_order_details');
    }
}
