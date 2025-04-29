<?php

declare(strict_types=1);

namespace Itiden\Transfinder;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use SplFileInfo;

final readonly class Transfinder
{
    public function discoverTranslations(): Collection
    {
        return collect(Config::get('transfinder.lang_paths'))
            ->flatMap(
                static fn(string $path) => collect(File::allFiles($path))
                    ->map(
                        static fn(SplFileInfo $file) => [
                            'path' => $file->getPathname(),
                            'lang' => str(substr(pathinfo($file->getPathname(), PATHINFO_DIRNAME), strlen($path) + 1))->replace('/', '.')->toString(),
                        ]
                    )
            )
            ->reduceWithKeys(static function (Collection $acc, array $file) {
                $key = implode('.', [$file['lang'], pathinfo($file['path'], PATHINFO_FILENAME)]);

                $acc[$key] = require $file['path'];

                return $acc;
            }, collect())->undot();
    }

    public function discoverUsedTranslationKeys(): Collection
    {
        return collect(Config::get('transfinder.script_paths'))
            ->flatMap(File::allFiles(...))
            ->flatMap(static function (SplFileInfo $file) {
                if ($file->getFilename() === 'translations.ts') {
                    return [];
                }

                $content = $file->getContents();

                preg_match_all('/t\(\s*([\'"])(.*?)\1/', $content, $matches);

                return collect($matches[2]);
            });
    }

    private function compileTypeOverloads(Collection $translations): string
    {
        return collect($translations->first())->dot()->map(static function (string $value, string $key) {
            $params = [];
            preg_match_all('/:(\w+)/', $value, $matches);
            if (! empty($matches[1])) {
                $params = $matches[1];
            }

            if ($params) {
                $paramStr = implode(', ', array_map(fn($p) => "{$p}: string | number", $params));
                return "(key: \"{$key}\", params: { {$paramStr} }): string;";
            } else {
                return "(key: \"{$key}\"): string;";
            }
        })->implode('');
    }

    public function compile(
        Collection $availableTranlsations,
        Collection $keysToKeep,
    ): string {
        $translationsToIncludeInBundle = json_encode($availableTranlsations->mapWithKeys(static function (array $value, string $key) use ($keysToKeep) {
            $value = collect($value)->dot();

            $value = $value->filter(static function ($_, string $key) use ($keysToKeep) {
                return $keysToKeep->has($key);
            });

            return [$key => $value->toArray()];
        }), JSON_THROW_ON_ERROR);

        return <<<ts
            // This file is auto-generated. Do not edit it manually.
            /* eslint-disable */

            const translations = $translationsToIncludeInBundle;

            type TranslationFunction = {
            {$this->compileTypeOverloads($availableTranlsations)}
            };

            export const t: TranslationFunction = (
                key: string,
                params?: Record<string, string | number>
            ): string => {
                const lang = document.documentElement.lang as keyof typeof translations;

                const value = translations[lang][key];

                if (typeof value !== "string") {
                    console.warn(`Translation key "\${key}" not found`);
                    return key;
                }

                if (!params) {
                    return value;
                }

                return Object.entries(params).reduce(
                    (str, [param, value]) => str.replace(`:\${param}`, String(value)),
                    value
                );
            };

            ts;
    }
}
