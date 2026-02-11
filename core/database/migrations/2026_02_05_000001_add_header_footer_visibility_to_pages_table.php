<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Central pages table (landlord)
        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table) {
                if (!Schema::hasColumn('pages', 'show_header')) {
                    $table->boolean('show_header')->default(true)->nullable();
                }
                if (!Schema::hasColumn('pages', 'show_footer')) {
                    $table->boolean('show_footer')->default(true)->nullable();
                }
                if (!Schema::hasColumn('pages', 'show_social_header')) {
                    $table->boolean('show_social_header')->default(true)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table) {
                foreach (['show_header', 'show_footer', 'show_social_header'] as $column) {
                    if (Schema::hasColumn('pages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

