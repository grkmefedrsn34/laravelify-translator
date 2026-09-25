<?php

namespace Laravelify\Translator\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class ScannerService
{
    protected array $patterns = [
        '/\b__\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]/',
        '/\btrans\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]/',
        '/\btrans_choice\(\s*[\'"]([^\'"]+)[\'"]\s*,/',
        '/@lang\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
    ];

    public function __construct(
        protected Filesystem $files
    ) {}

    public function scan(): Collection
    {
        $paths = config('laravelify-translator.scan.paths');
        $keys  = collect();

        foreach ($paths as $path) {
            $fullPath = base_path($path);

            if (! $this->files->isDirectory($fullPath)) {
                continue;
            }

            foreach ($this->files->allFiles($fullPath) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $found = $this->extractKeys($file->getContents());

                foreach ($found as $key) {
                    $keys->put($key, $key);
                }
            }
        }

        return $keys->sortKeys();
    }

    protected function extractKeys(string $content): array
    {
        $keys = [];

        foreach ($this->patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            foreach ($matches[1] as $match) {
                if (! str_contains($match, '$')) {
                    $keys[] = $match;
                }
            }
        }

        return array_unique($keys);
    }
}