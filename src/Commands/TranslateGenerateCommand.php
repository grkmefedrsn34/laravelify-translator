<?php

namespace Laravelify\Translator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Laravelify\Translator\Services\ScannerService;
use Laravelify\Translator\Services\TranslationService;

class TranslateGenerateCommand extends Command
{
    protected $signature = 'translate:generate
        {--force : Re-translate all keys, ignore cache}
        {--dry-run : Show what would be generated without writing files}';

    protected $description = 'Translate scanned keys and generate language files';

    protected array $cache = [];

    public function __construct(
        protected ScannerService $scanner,
        protected TranslationService $translator,
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $source  = config('laravelify-translator.source_locale', 'en');
        $locales = config('laravelify-translator.target_locales', ['tr']);
        $format  = config('laravelify-translator.output_format', 'both');
        $dryRun  = $this->option('dry-run');

        if (! $this->option('force')) {
            $this->loadCache();
        }

        $this->info('Scanning keys...');
        $keys = $this->scanner->scan();

        if ($keys->isEmpty()) {
            $this->warn('No keys found.');
            return self::FAILURE;
        }

        $this->info("Found {$keys->count()} key(s). Translating...");

        foreach ($locales as $locale) {
            $this->processLocale($locale, $source, $keys->toArray(), $format, $dryRun);
        }

        if (! $dryRun) {
            $this->saveCache();
        }

        $this->info('Done!');
        return self::SUCCESS;
    }

    protected function processLocale(string $locale, string $source, array $keys, string $format, bool $dryRun): void
    {
        $this->info("── Locale: {$locale}");

        $cacheKey    = "{$source}_{$locale}";
        $toTranslate = [];

        foreach ($keys as $key) {
            if (! isset($this->cache[$cacheKey][$key])) {
                $toTranslate[$key] = $key;
            }
        }

        if (! empty($toTranslate)) {
            $translated = $this->translator->translateBatch($toTranslate, $locale, $source);
            foreach ($translated as $key => $value) {
                $this->cache[$cacheKey][$key] = $value;
            }
        }

        $allTranslations = [];
        foreach ($keys as $key) {
            $allTranslations[$key] = $this->cache[$cacheKey][$key] ?? $key;
        }

        if ($format === 'json' || $format === 'both') {
            $this->writeJson($locale, $allTranslations, $dryRun);
        }

        if ($format === 'php' || $format === 'both') {
            $this->writePhp($locale, $allTranslations, $dryRun);
        }
    }

    protected function writeJson(string $locale, array $translations, bool $dryRun): void
    {
        $path    = lang_path("{$locale}.json");
        $content = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($dryRun) {
            $this->line("[DRY-RUN] Would write: {$path}");
            return;
        }

        $this->files->put($path, $content);
        $this->info("JSON → {$path}");
    }

    protected function writePhp(string $locale, array $translations, bool $dryRun): void
    {
        $dir  = lang_path($locale);
        $path = "{$dir}/messages.php";

        if ($dryRun) {
            $this->line("[DRY-RUN] Would write: {$path}");
            return;
        }

        $this->files->ensureDirectoryExists($dir);
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        $this->files->put($path, $content);
        $this->info("PHP  → {$path}");
    }

    protected function loadCache(): void
    {
        $path = config('laravelify-translator.cache.path');
        if ($this->files->exists($path)) {
            $this->cache = json_decode($this->files->get($path), true) ?? [];
        }
    }

    protected function saveCache(): void
    {
        $path = config('laravelify-translator.cache.path');
        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, json_encode($this->cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}