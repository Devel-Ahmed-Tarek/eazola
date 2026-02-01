<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('about');
            $table->string('icon')->nullable()->after('short_description');
            $table->string('image')->nullable()->after('icon');
            $table->integer('sort_order')->default(0)->after('image');
            $table->string('is_featured')->nullable()->after('sort_order');
            $table->string('status')->default('1')->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'icon',
                'image',
                'sort_order',
                'is_featured',
                'status'
            ]);
        });
    }
};
