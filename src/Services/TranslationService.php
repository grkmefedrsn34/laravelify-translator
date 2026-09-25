<?php

namespace Laravelify\Translator\Services;

use GuzzleHttp\Client;

class TranslationService
{
    protected Client $client;
    protected string $engine;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->engine = config('laravelify-translator.engine', 'mymemory');
    }

    public function translate(string $text, string $target, string $source = 'en'): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        return match($this->engine) {
            'google_free' => $this->translateGoogle($text, $target, $source),
            default       => $this->translateMyMemory($text, $target, $source),
        };
    }

    public function translateBatch(array $texts, string $target, string $source = 'en'): array
    {
        $results = [];

        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate($text, $target, $source);
            usleep(300000); // 300ms bekle, rate limit için
        }

        return $results;
    }

    protected function translateMyMemory(string $text, string $target, string $source): string
    {
        $response = $this->client->get('https://api.mymemory.translated.net/get', [
            'query' => [
                'q'        => $text,
                'langpair' => "{$source}|{$target}",
                'de'       => config('laravelify-translator.mymemory.api_key', ''),
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        return $body['responseData']['translatedText'] ?? $text;
    }

    protected function translateGoogle(string $text, string $target, string $source): string
    {
        $response = $this->client->get('https://translate.googleapis.com/translate_a/single', [
            'query' => [
                'client' => 'gtx',
                'sl'     => $source,
                'tl'     => $target,
                'dt'     => 't',
                'q'      => $text,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $translated = '';
        foreach ($body[0] as $part) {
            $translated .= $part[0] ?? '';
        }

        return $translated ?: $text;
    }
}