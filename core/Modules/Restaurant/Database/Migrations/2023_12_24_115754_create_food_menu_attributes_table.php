<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoodMenuAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('food_menu_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->string('value');
            $table->string('extra_price');
            $table->foreignId('food_menu_id')->constrained('food_menus')->cascadeOnDelete();
            $table->foreignId('menu_attribute_id')->constrained('menu_attributes')->cascadeOnDelete();
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
        Schema::dropIfExists('food_menu_attributes');
    }
}
