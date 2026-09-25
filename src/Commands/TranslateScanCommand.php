<?php

namespace Laravelify\Translator\Commands;

use Illuminate\Console\Command;
use Laravelify\Translator\Services\ScannerService;

class TranslateScanCommand extends Command
{
   protected $signature = 'translate:scan
        {--show : Print all found keys to terminal}';

    protected $description = 'Scan your Laravel project for translation keys';

    public function __construct(
        protected ScannerService $scanner
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Scanning for translation keys...');

        $keys = $this->scanner->scan();

        if ($keys->isEmpty()) {
            $this->warn('No translation keys found.');
            return self::FAILURE;
        }

        $this->info("Found {$keys->count()} key(s).");

        if ($this->option('show')) {
            foreach ($keys as $key) {
                $this->line("  - {$key}");
            }
        }

        return self::SUCCESS;
    }
}