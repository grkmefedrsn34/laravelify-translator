<?php

return [

    'source_locale' => env('TRANSLATOR_SOURCE_LOCALE', 'en'),

    'target_locales' => explode(',', env('TRANSLATOR_LOCALES', 'tr,de')),

    'output_format' => env('TRANSLATOR_FORMAT', 'both'),

    'engine' => env('TRANSLATOR_ENGINE', 'mymemory'),

    'mymemory' => [
        'api_key' => env('TRANSLATOR_MYMEMORY_API_KEY', ''), // opsiyonel, kotayı artırır
    ],

    'google_free' => [
        'timeout' => 10,
    ],

    'scan' => [
        'paths' => [
            'resources/views',
            'app',
        ],
        'extensions' => ['php'],
        'exclude_keys' => [],
    ],

    'cache' => [
        'enabled' => env('TRANSLATOR_CACHE', true),
        'path'    => storage_path('app/laravelify-translator-cache.json'),
    ],

];