<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiCustomPageSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('ai_custom_page_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('entity_name', 120)->nullable();
            $table->json('payload_json');
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['page_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_custom_page_submissions');
    }
}
