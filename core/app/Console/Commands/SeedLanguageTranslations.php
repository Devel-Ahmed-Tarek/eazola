<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedLanguageTranslations extends Command
{
    protected $signature = 'translations:seed
                            {locale=ar : Language slug to restore, e.g. ar}
                            {--force : Overwrite the target language file}';

    protected $description = 'Restore UI translation JSON from resources/lang/seed/{locale}.json';

    public function handle(): int
    {
        $locale = trim((string) $this->argument('locale'));
        if ($locale === '') {
            $this->error('Locale is required.');

            return self::FAILURE;
        }

        $seedPath = resource_path('lang/seed/' . $locale . '.json');
        $targetPath = resource_path('lang/' . $locale . '.json');

        if (!file_exists($seedPath)) {
            $this->error("Seed file not found: {$seedPath}");
            $this->line('Expected path: resources/lang/seed/' . $locale . '.json');

            return self::FAILURE;
        }

        if (file_exists($targetPath) && !$this->option('force')) {
            if (!$this->confirm("Overwrite existing file {$targetPath}?", true)) {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
        }

        $content = file_get_contents($seedPath);
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            $this->error('Seed file is not valid JSON.');

            return self::FAILURE;
        }

        $langDirectory = resource_path('lang');
        if (!is_dir($langDirectory) && !@mkdir($langDirectory, 0775, true) && !is_dir($langDirectory)) {
            $this->error("Unable to create directory: {$langDirectory}");

            return self::FAILURE;
        }

        $encoded = json_encode(
            $decoded,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($encoded === false || @file_put_contents($targetPath, $encoded . PHP_EOL) === false) {
            $this->error("Unable to write translation file: {$targetPath}");
            $this->line('Fix permissions, e.g.: chown -R www-data:www-data resources/lang && chmod -R 775 resources/lang');

            return self::FAILURE;
        }

        $this->info("Restored {$locale}.json from seed (" . count($decoded) . ' strings).');
        $this->line('Run: php artisan cache:clear && php artisan view:clear');

        return self::SUCCESS;
    }
}
