<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ai_custom_page_blueprints')) {
            return;
        }

        Schema::create('ai_custom_page_blueprints', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('page_id')->unique();
            $table->string('mode', 30)->default('structured');
            $table->string('entity_name', 120)->nullable();
            $table->json('schema_json')->nullable();
            $table->json('data_bindings')->nullable();
            $table->json('required_routes')->nullable();
            $table->longText('sanitized_html')->nullable();
            $table->longText('ai_prompt')->nullable();
            $table->timestamps();

            $table->index('page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_custom_page_blueprints');
    }
};
