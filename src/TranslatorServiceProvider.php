<?php

namespace Laravelify\Translator;

use Illuminate\Support\ServiceProvider;
use Laravelify\Translator\Services\ScannerService;
use Laravelify\Translator\Services\TranslationService;
use Laravelify\Translator\Commands\TranslateScanCommand;
use Laravelify\Translator\Commands\TranslateGenerateCommand;
use Laravelify\Translator\Commands\TranslateRunCommand;
use Laravelify\Translator\Commands\TranslateMissingCommand;

class TranslatorServiceProvider extends ServiceProvider
{
   public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravelify-translator.php',
            'laravelify-translator'
        );

        $this->app->singleton(ScannerService::class);
        $this->app->singleton(TranslationService::class);
    }   

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravelify-translator.php' => config_path('laravelify-translator.php'),
            ], 'laravelify-translator-config');
    
            $this->commands([
                TranslateScanCommand::class,
                TranslateGenerateCommand::class,
                TranslateRunCommand::class,
                TranslateMissingCommand::class, // bunu ekle
            ]);
        }
    }
}
