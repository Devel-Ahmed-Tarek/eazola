<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Fix is_featured column type to match is_popular (string nullable)
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Change is_featured from boolean to string nullable (same as is_popular)
            $table->string('is_featured')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->change();
        });
    }
};
