<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meta_infos', function (Blueprint $table) {
            $table->text('fb_description')->nullable()->change();
            $table->text('tw_description')->nullable()->change();
        });
    }
};
