<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'short_description')) {
                $table->text('short_description')->nullable()->after('about');
            }
            if (!Schema::hasColumn('hotels', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (!Schema::hasColumn('hotels', 'is_featured')) {
                $table->string('is_featured')->nullable();
            }
            // Skip 'status' - already exists in table
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $columns = ['short_description', 'icon', 'image', 'sort_order', 'is_featured'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('hotels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
