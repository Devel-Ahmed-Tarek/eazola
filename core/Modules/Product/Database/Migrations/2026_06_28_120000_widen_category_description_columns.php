<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'sub_categories', 'child_categories'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'description')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'sub_categories', 'child_categories'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'description')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyText('description')->nullable()->change();
            });
        }
    }
};
