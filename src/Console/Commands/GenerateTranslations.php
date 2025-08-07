<?php

declare(strict_types=1);

namespace Itiden\Polywarp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Itiden\Polywarp\Polywarp;

final class GenerateTranslations extends Command
{
    protected $signature = 'polywarp:generate {--use-cache-for=none}';

    protected $description = 'Generate typescript translation files for the application';

    private const string AVAILABLE_TRANSLATIONS_CACHE_KEY = 'polywarp.available_translations';
    private const string USED_KEYS_CACHE_KEY = 'polywarp.used_translation_keys';

    public function handle(Polywarp $polywarp): int
    {
        $input = Validator::make(
            data: $this->options(),
            rules: [
                'use-cache-for' => ['required', 'string', 'in:available-translations,used-translations,none'],
            ],
        )->validate();

        $cache = Cache::store(name: 'file');

        $useCacheFor = $input['use-cache-for'];

        if (in_array($useCacheFor, ['available-translations', 'none'], strict: true)) {
            $cache->forget(key: self::USED_KEYS_CACHE_KEY);
        }
        if (in_array($useCacheFor, ['used-translations', 'none'], strict: true)) {
            $cache->forget(key: self::AVAILABLE_TRANSLATIONS_CACHE_KEY);
        }

        $outFile = Config::get(key: 'polywarp.output_path');

        File::ensureDirectoryExists(pathinfo($outFile, PATHINFO_DIRNAME));

        File::put($outFile, $polywarp->compile(
            availableTranlsations: $cache->rememberForever(
                key: self::AVAILABLE_TRANSLATIONS_CACHE_KEY,
                callback: fn(): Collection => $polywarp->discoverTranslations(),
            ),
            keysToKeep: $cache->rememberForever(
                key: self::USED_KEYS_CACHE_KEY,
                callback: fn(): Collection => $polywarp->discoverUsedTranslationKeys(),
            ),
        ));

        $this->info(string: 'Translations generated successfully.');

        return Command::SUCCESS;
    }
}
