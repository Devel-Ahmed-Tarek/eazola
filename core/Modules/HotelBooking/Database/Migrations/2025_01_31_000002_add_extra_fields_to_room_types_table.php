<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->string('slug')->nullable()->after('short_description');
            $table->string('icon')->nullable()->after('slug');
            $table->string('image')->nullable()->after('icon');
            $table->integer('sort_order')->default(0)->after('image');
            $table->string('is_featured')->nullable()->after('sort_order');
            $table->string('status')->default('1')->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'slug',
                'icon',
                'image',
                'sort_order',
                'is_featured',
                'status'
            ]);
        });
    }
};
