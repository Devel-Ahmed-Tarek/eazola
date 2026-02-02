<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'short_description')) {
                $table->text('short_description')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'slug')) {
                $table->string('slug')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('rooms', 'is_featured')) {
                $table->string('is_featured')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'is_popular')) {
                $table->string('is_popular')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'gallery')) {
                $table->text('gallery')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'video_url')) {
                $table->string('video_url')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'max_guests')) {
                $table->integer('max_guests')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (!Schema::hasColumn('rooms', 'status')) {
                $table->string('status')->default('1');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $columns = ['short_description', 'slug', 'sale_price', 'is_featured', 'is_popular', 'gallery', 'video_url', 'max_guests', 'sort_order', 'status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('rooms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
