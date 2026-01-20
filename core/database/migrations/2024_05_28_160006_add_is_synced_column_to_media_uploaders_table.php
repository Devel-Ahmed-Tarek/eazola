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
        Schema::table('media_uploaders', function (Blueprint $table) {
            if (!Schema::hasColumn("media_uploaders","is_synced")){
                $table->unsignedBigInteger("is_synced")->default(0)->after('dimensions');
            }
            if (!Schema::hasColumn("media_uploaders","load_from")){
                $table->unsignedBigInteger('load_from')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_uploaders', function (Blueprint $table) {
            if (Schema::hasColumn("media_uploaders","is_synced")){
                $table->dropColumn("is_synced");
            }
            if (Schema::hasColumn("media_uploaders","load_from")){
                $table->dropColumn('load_from');
            }
        });
    }
};
