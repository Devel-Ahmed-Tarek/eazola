<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'short_description')) {
                $table->text('short_description')->nullable();
            }
            if (!Schema::hasColumn('room_types', 'slug')) {
                $table->string('slug')->nullable();
            }
            if (!Schema::hasColumn('room_types', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (!Schema::hasColumn('room_types', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('room_types', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (!Schema::hasColumn('room_types', 'is_featured')) {
                $table->string('is_featured')->nullable();
            }
            if (!Schema::hasColumn('room_types', 'status')) {
                $table->string('status')->default('1');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $columns = ['short_description', 'slug', 'icon', 'image', 'sort_order', 'is_featured', 'status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('room_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
