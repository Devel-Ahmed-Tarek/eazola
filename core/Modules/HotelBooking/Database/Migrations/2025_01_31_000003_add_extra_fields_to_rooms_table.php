<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->string('slug')->nullable()->after('short_description');
            $table->decimal('sale_price', 10, 2)->nullable()->after('base_cost');
            $table->string('is_featured')->nullable()->after('sale_price');
            $table->string('is_popular')->nullable()->after('is_featured');
            $table->text('gallery')->nullable()->after('is_popular');
            $table->string('video_url')->nullable()->after('gallery');
            $table->integer('max_guests')->nullable()->after('video_url');
            $table->integer('sort_order')->default(0)->after('max_guests');
            $table->string('status')->default('1')->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'slug',
                'sale_price',
                'is_featured',
                'is_popular',
                'gallery',
                'video_url',
                'max_guests',
                'sort_order',
                'status'
            ]);
        });
    }
};
