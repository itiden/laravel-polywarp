<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use SplFileInfo;

final readonly class Polywarp
{
    public function discoverTranslations(): Collection
    {
        return collect(app('translator')->getLoader()->paths())
            ->flatMap(static fn(string $path): Collection => collect(File::allFiles($path))
                ->map(static fn(SplFileInfo $file): array => [
                    'path' => $file->getPathname(),
                    'lang' => str(substr(pathinfo($file->getPathname(), PATHINFO_DIRNAME), strlen($path) + 1))
                        ->replace('/', '.')
                        ->toString(),
                ]))
            ->reduceWithKeys(static function (Collection $acc, array $file): Collection {
                $key = implode('.', [$file['lang'], pathinfo($file['path'], PATHINFO_FILENAME)]);

                if (pathinfo($file['path'], PATHINFO_EXTENSION) !== 'php') {
                    return $acc;
                }

                $acc[$key] = match (pathinfo($file['path'], PATHINFO_EXTENSION)) {
                    'json' => json_decode($file['path'], true, JSON_THROW_ON_ERROR),
                    'php' => require $file['path'],
                    default => [],
                };

                return $acc;
            }, collect())
            ->undot()
            ->map(static function (array $value): array {
                return collect($value)->dot()->filter(fn($trans) => is_string($trans))->toArray();
            });
    }

    public function discoverUsedTranslationKeys(): Collection
    {
        $outputPath = Config::get('polywarp.output_path');

        return collect(Config::get('polywarp.script_paths'))
            ->flatMap(File::allFiles(...))
            ->flatMap(static function (SplFileInfo $file) use ($outputPath): Collection {
                if ($file->getExtension() !== 'ts' || $file->getPathname() === $outputPath) {
                    return collect();
                }

                $content = $file->getContents();

                preg_match_all('/t\(\s*([\'"])(.*?)\1/', $content, $matches);

                return collect($matches[2]);
            });
    }

    private function compileTypeOverloads(Collection $translations): string
    {
        return $translations
            ->flatMap(fn($e) => $e)
            ->map(static function (string $value, string $key): string {
                $params = [];
                preg_match_all('/:(\w+)/', $value, $matches);
                if (isset($matches[1])) {
                    $params = $matches[1];
                }

                if ($params) {
                    $paramStr = implode(', ', array_map(fn(string $p): string => "{$p}: string | number", $params));
                    return "(key: \"{$key}\", params: { {$paramStr} }): string;";
                }

                return "(key: \"{$key}\"): string;";
            })
            ->implode('');
    }

    public function compile(Collection $availableTranlsations, Collection $keysToKeep): string
    {
        $translationsToIncludeInBundle = json_encode(
            $availableTranlsations->mapWithKeys(static fn(
                array $value,
                string $lang,
            ): array => [$lang => array_filter($value, $keysToKeep->contains(...), mode: ARRAY_FILTER_USE_KEY)]),
            JSON_THROW_ON_ERROR
        );

        return <<<ts
        // This file is auto-generated. Do not edit it manually.
        /* eslint-disable */

        const translations = {$translationsToIncludeInBundle};

        type TranslationFunction = {
        {$this->compileTypeOverloads($availableTranlsations)}
        };

        export const t: TranslationFunction = (
            key: string,
            params?: Record<string, string | number>
        ): string => {
            const lang = document.documentElement.lang as keyof typeof translations;

            const value = translations[lang][key as keyof (typeof translations)[typeof lang]];

            if (typeof value !== "string") {
                console.warn(`Translation key "\${key}" not found`);
                return key;
            }

            if (!params) {
                return value;
            }

            return Object.entries(params).reduce(
                (str, [param, value]) => str.replace(`:\${param}`, String(value)),
                String(value)
            );
        };

        ts;
    }
}
