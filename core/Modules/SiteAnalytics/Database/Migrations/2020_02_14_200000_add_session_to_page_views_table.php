<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionToPageViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('page_views', 'session'))
        {
            Schema::table('page_views', function (Blueprint $table) {
                $table->string('session')->nullable()->after('uri');
            });
        }
    }
}
