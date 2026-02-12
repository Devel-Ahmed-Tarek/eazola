<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider', 50)->default('sideup')->index();
            $table->string('api_key')->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('enabled')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_accounts');
    }
};

