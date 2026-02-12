<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ربط الطلب
            $table->unsignedBigInteger('order_id')->index();

            // مزوّد الشحن (sideup حالياً، وبعدين ممكن نزود غيره)
            $table->string('provider', 50)->default('sideup')->index();

            // بيانات من مزوّد الشحن
            $table->string('external_shipment_id')->nullable()->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('carrier_name')->nullable();
            $table->string('service_type')->nullable(); // standard / express ..etc

            // حالة الشحنة عندنا
            $table->string('status', 50)->default('created')->index();

            // روابط مساعدة
            $table->string('label_url')->nullable();
            $table->string('tracking_url')->nullable();

            // أرقام للتقارير
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();

            $table->json('meta')->nullable(); // أي بيانات إضافية من الـ API

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

