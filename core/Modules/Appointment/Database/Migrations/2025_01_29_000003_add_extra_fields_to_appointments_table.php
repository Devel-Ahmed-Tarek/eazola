<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds extra fields to appointments table for enhanced functionality
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->integer('duration')->nullable()->after('price'); // Duration in minutes
            $table->decimal('sale_price', 10, 2)->nullable()->after('duration'); // Sale/discount price
            $table->string('is_featured')->nullable()->after('is_popular'); // Same type as is_popular
            $table->text('gallery')->nullable()->after('image'); // JSON array of image IDs
            $table->string('video_url', 500)->nullable()->after('gallery');
            $table->integer('max_booking_per_slot')->default(1)->after('person'); // Max bookings per time slot
            $table->integer('advance_booking_days')->default(30)->after('max_booking_per_slot'); // Days in advance
            $table->text('cancellation_policy')->nullable()->after('advance_booking_days');
            $table->text('requirements')->nullable()->after('cancellation_policy'); // Requirements before booking
            $table->integer('sort_order')->default(0)->after('requirements');
            $table->decimal('rating_avg', 3, 2)->default(0)->after('sort_order'); // Average rating
            $table->integer('rating_count')->default(0)->after('rating_avg'); // Number of ratings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'duration',
                'sale_price',
                'is_featured',
                'gallery',
                'video_url',
                'max_booking_per_slot',
                'advance_booking_days',
                'cancellation_policy',
                'requirements',
                'sort_order',
                'rating_avg',
                'rating_count',
            ]);
        });
    }
};
