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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'approval_status')) {
                // pending, approved, rejected
                $table->string('approval_status')->default('approved')->after('expire_date');
            }
            if (!Schema::hasColumn('tenants', 'approval_note')) {
                $table->text('approval_note')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('tenants', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_note');
            }
            if (!Schema::hasColumn('tenants', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = ['approval_status', 'approval_note', 'approved_at', 'approved_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
