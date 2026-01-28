<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores per-tenant sidebar menu visibility settings.
     * If a record exists with is_visible = 0, the menu item will be hidden for that tenant.
     * If no record exists, the menu item follows the default behavior (visible based on plan).
     */
    public function up(): void
    {
        Schema::create('tenant_menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('menu_key')->index();
            $table->string('menu_label')->nullable();
            $table->string('parent_key')->nullable();
            $table->boolean('is_visible')->default(1);
            $table->timestamps();

            // Unique constraint: one setting per menu per tenant
            $table->unique(['tenant_id', 'menu_key']);

            // Foreign key to tenants table
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_menu_settings');
    }
};
