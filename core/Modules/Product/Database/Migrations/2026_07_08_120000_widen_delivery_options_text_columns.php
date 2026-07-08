<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('delivery_options')) {
            return;
        }

        Schema::table('delivery_options', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_options', 'title')) {
                $table->text('title')->change();
            }
            if (Schema::hasColumn('delivery_options', 'sub_title')) {
                $table->text('sub_title')->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('delivery_options')) {
            return;
        }

        Schema::table('delivery_options', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_options', 'title')) {
                $table->string('title')->change();
            }
            if (Schema::hasColumn('delivery_options', 'sub_title')) {
                $table->string('sub_title')->change();
            }
        });
    }
};
