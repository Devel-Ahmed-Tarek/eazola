<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds extra fields to appointment_subcategories table for hierarchical display
     */
    public function up(): void
    {
        Schema::table('appointment_subcategories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('slug')->nullable()->after('description');
            $table->string('image')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('image'); // Font Awesome icon class
            $table->integer('sort_order')->default(0)->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_subcategories', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'slug',
                'image',
                'icon',
                'sort_order',
            ]);
        });
    }
};
