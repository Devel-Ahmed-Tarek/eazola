<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoodMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('food_menus', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('slug')->index();
            $table->string('sku')->nullable();
            $table->text('tag')->nullable();
            $table->text('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('policy_description')->nullable();
            $table->string('image_id')->nullable();
            $table->double('regular_price')->nullable();
            $table->double('sale_price')->nullable();
            $table->double('cost')->nullable();
            $table->boolean('status')->default(1);
            $table->integer('sold_count')->default("0");
            $table->integer('min_purchase')->nullable();
            $table->integer('max_purchase')->nullable();
            $table->boolean('is_refundable')->index()->nullable();
            $table->boolean('is_orderable')->index()->default(1)->comment("only for display food item");
            $table->boolean('is_inventory_warn_able')->index()->nullable();
            $table->foreignId('menu_tax_id')->cascadeOnDelete();
            $table->foreignId('menu_category_id')->constrained('menu_categories')->cascadeOnDelete();
            $table->integer('menu_sub_category_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('food_menus');
    }
}
