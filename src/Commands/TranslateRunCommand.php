<?php

namespace Laravelify\Translator\Commands;

use Illuminate\Console\Command;

class TranslateRunCommand extends Command
{
    protected $signature = 'translate:run
        {--force   : Re-translate all keys}
        {--dry-run : Simulate without writing files}';

    protected $description = 'Scan + translate + generate in one command';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== Laravelify Translator ===');
        $this->info('');

        $this->info('Step 1/2: Scanning...');
        $scanResult = $this->call('translate:scan');

        if ($scanResult !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info('');
        $this->info('Step 2/2: Translating...');

        $args = [];

        if ($this->option('force')) {
            $args['--force'] = true;
        }

        if ($this->option('dry-run')) {
            $args['--dry-run'] = true;
        }

        return $this->call('translate:generate', $args);
    }
}