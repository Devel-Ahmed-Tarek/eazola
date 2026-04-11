<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brands')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->text('url')->nullable()->change();
        });

        $default = \App\Facades\GlobalLanguage::default_slug();
        foreach (DB::table('brands')->select('id', 'url')->cursor() as $row) {
            $url = $row->url;
            if ($url === null || $url === '') {
                continue;
            }
            if (is_array(json_decode($url, true))) {
                continue;
            }
            DB::table('brands')->where('id', $row->id)->update([
                'url' => json_encode([$default => $url], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('brands')) {
            return;
        }

        $default = \App\Facades\GlobalLanguage::default_slug();
        foreach (DB::table('brands')->select('id', 'url')->cursor() as $row) {
            $decoded = json_decode($row->url ?? '', true);
            if (! is_array($decoded)) {
                continue;
            }
            $plain = (string) ($decoded[$default] ?? reset($decoded) ?? '');
            DB::table('brands')->where('id', $row->id)->update(['url' => $plain]);
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
        });
    }
};
