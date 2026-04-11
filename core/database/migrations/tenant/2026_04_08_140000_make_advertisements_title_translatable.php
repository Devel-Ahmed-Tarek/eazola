<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->text('title')->nullable()->change();
        });

        $default = \App\Facades\GlobalLanguage::default_slug();
        foreach (DB::table('advertisements')->select('id', 'title')->cursor() as $row) {
            $title = $row->title;
            if ($title === null || $title === '') {
                continue;
            }
            if (is_array(json_decode($title, true))) {
                continue;
            }
            DB::table('advertisements')->where('id', $row->id)->update([
                'title' => json_encode([$default => $title], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        $default = \App\Facades\GlobalLanguage::default_slug();
        foreach (DB::table('advertisements')->select('id', 'title')->cursor() as $row) {
            $decoded = json_decode($row->title ?? '', true);
            if (! is_array($decoded)) {
                continue;
            }
            $plain = (string) ($decoded[$default] ?? reset($decoded) ?? '');
            DB::table('advertisements')->where('id', $row->id)->update(['title' => $plain]);
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });
    }
};
