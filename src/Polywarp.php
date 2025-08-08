<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use SplFileInfo;

final readonly class Polywarp
{
    public function __construct(
        private TranslationDirectoriesResolver $translationDirectoriesResolver,
    ) {}

    public function discoverTranslations(): Collection
    {
        return collect($this->translationDirectoriesResolver->resolve())
            ->flatMap(static fn(string $path): Collection => collect(File::allFiles(
                $path,
            ))->map(static function (SplFileInfo $file) use ($path): array {
                if ($file->getExtension() === 'json') {
                    return [
                        'path' => $file->getPathname(),
                        'lang' => $file->getFilenameWithoutExtension(),
                    ];
                }

                $langPath = str(str_replace($path, replace: '', subject: $file->getPathname()))
                    ->beforeLast(search: '.php');
                $lang = $langPath->betweenFirst(
                    from: '/',
                    to: '/',
                )->toString();

                return [
                    'path' => $file->getPathname(),
                    'key' => $langPath->after($lang)->explode(delimiter: '/')->filter()->implode(value: '.'),
                    'lang' => $lang,
                ];
            }))
            ->reduceWithKeys(static function (Collection $acc, array $file): Collection {
                $lang = $file['lang'];

                $acc[$lang] = array_merge($acc[$lang] ?? [], match (pathinfo($file['path'], PATHINFO_EXTENSION)) {
                    'json' => json_decode(
                        file_get_contents($file['path']),
                        associative: true,
                        flags: JSON_THROW_ON_ERROR,
                    ),
                    'php' => [$file['key'] => require $file['path']],
                    default => [],
                });

                return $acc;
            }, collect())
            ->map(static function (array $value): array {
                return collect($value)->dot()->filter(fn(mixed $trans): bool => is_string($trans))->toArray();
            });
    }

    public function discoverUsedTranslationKeys(): Collection
    {
        $outputPath = Config::string(key: 'polywarp.output_path');

        return collect(Config::array(key: 'polywarp.content_paths'))
            ->flatMap(File::allFiles(...))
            ->flatMap(static function (SplFileInfo $file) use ($outputPath): Collection {
                if (!in_array(
                        needle: $file->getExtension(),
                        haystack: Config::array(key: 'polywarp.extenstion_to_scan'),
                        strict: true,
                    ) || $file->getPathname() === $outputPath) {
                    return collect();
                }

                $content = $file->getContents();

                preg_match_all(
                    pattern: '/t\(\s*([\'"])(.*?)\1/',
                    subject: $content,
                    matches: $matches,
                );

                return collect($matches[2]);
            });
    }

    private function compileTypeOverloads(Collection $translations): string
    {
        return $translations
            ->collapse()
            ->map(static function (string $value, string $key): string {
                $params = [];
                preg_match_all(
                    pattern: '/:(\w+)/',
                    subject: $value,
                    matches: $matches,
                );

                $params = array_unique($matches[1] ?? []);

                if (count($params) > 0) {
                    $paramStr = implode(
                        separator: ', ',
                        array: array_map(fn(string $p): string => "{$p}: string | number", $params),
                    );

                    return "(key: `{$key}`, params: { {$paramStr} }): string;";
                }

                return "(key: `{$key}`): string;";
            })
            ->implode(value: '');
    }

    public function compile(Collection $availableTranlsations, Collection $keysToKeep): string
    {
        $translationsToIncludeInBundle = json_encode(
            $availableTranlsations->mapWithKeys(static fn(array $value, string $lang): array => [$lang => array_filter(
                $value,
                $keysToKeep->contains(...),
                mode: ARRAY_FILTER_USE_KEY,
            )]),
            JSON_THROW_ON_ERROR,
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
