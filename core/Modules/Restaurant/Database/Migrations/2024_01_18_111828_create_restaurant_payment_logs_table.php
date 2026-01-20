<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->boolean('order_status')->default(1);
            $table->string('payment_status')->default("pending");
            $table->string('coupon_type')->nullable();
            $table->string('coupon_code')->nullable();
            $table->double('coupon_discount')->nullable();
            $table->double('tax_amount')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('total_amount');
            $table->string('payment_gateway')->nullable();
            $table->string('status')->default(0);
            $table->text('transaction_id')->nullable();
            $table->foreignId('menu_order_id')->constrained('menu_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('manual_payment_attachment')->nullable();
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
        Schema::dropIfExists('restaurant_payment_logs');
    }
}
