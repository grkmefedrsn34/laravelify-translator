<?php

namespace Laravelify\Translator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Laravelify\Translator\Services\ScannerService;
use Laravelify\Translator\Services\TranslationService;

class TranslateMissingCommand extends Command
{
    protected $signature = 'translate:missing
        {--locale= : Check a specific locale}
        {--dry-run : Show missing keys without translating}';

    protected $description = 'Find and translate only missing translation keys';

    public function __construct(
        protected ScannerService $scanner,
        protected TranslationService $translator,
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $locales = $this->option('locale')
            ? [$this->option('locale')]
            : config('laravelify-translator.target_locales', ['tr']);

        $source = config('laravelify-translator.source_locale', 'en');
        $dryRun = $this->option('dry-run');

        $keys = $this->scanner->scan();

        if ($keys->isEmpty()) {
            $this->warn('No translation keys found.');
            return self::FAILURE;
        }

        foreach ($locales as $locale) {
            $this->processLocale($locale, $source, $keys->toArray(), $dryRun);
        }

        return self::SUCCESS;
    }

    protected function processLocale(string $locale, string $source, array $keys, bool $dryRun): void
    {
        $this->info("── Locale: {$locale}");

        // Mevcut çevirileri yükle
        $existing = $this->loadExisting($locale);

        // Eksik keyleri bul
        $missing = [];
        foreach ($keys as $key) {
            if (! isset($existing[$key])) {
                $missing[$key] = $key;
            }
        }

        if (empty($missing)) {
            $this->info("  ✅ No missing keys!");
            return;
        }

        $this->info("  Found " . count($missing) . " missing key(s):");
        foreach ($missing as $key) {
            $this->line("    - {$key}");
        }

        if ($dryRun) {
            return;
        }

        $this->info("  Translating...");
        $translated = $this->translator->translateBatch($missing, $locale, $source);

        // Mevcut dosyayla birleştir
        $merged = array_merge($existing, $translated);

        // JSON dosyasına yaz
        $path = lang_path("{$locale}.json");
        $this->files->put(
            $path,
            json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info("  ✅ Updated: {$path}");
    }

    protected function loadExisting(string $locale): array
    {
        $path = lang_path("{$locale}.json");

        if (! $this->files->exists($path)) {
            return [];
        }

        return json_decode($this->files->get($path), true) ?? [];
    }
}
