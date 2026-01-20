<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tenant_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->longText('user_details')->nullable()->comment('It will contain all data of the user converted into json');
            $table->ipAddress('ip_address')->comment('User ip address');
            $table->string('domain');
            $table->double('domain_price');
            $table->double('extra_fee')->default(0);
            $table->integer('period')->default(1);
            $table->boolean('privacy')->default(false);
            $table->string('payment_gateway')->nullable();
            $table->boolean('payment_status')->default(false);
            $table->boolean('status')->default(false)->comment('false means drafted and true means active');
            $table->longText('custom_field')->nullable();
            $table->string('track', 191)->nullable();
            $table->longText('contact_billing')->nullable();
            $table->longText('contact_registrant')->nullable();
            $table->longText('contact_tech')->nullable();
            $table->string('unique_key')->nullable();
            $table->unsignedInteger('purchase_count')->default(0);
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('tenant_id');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_payment_logs');
    }
}
