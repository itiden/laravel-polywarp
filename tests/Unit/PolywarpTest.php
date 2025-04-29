<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Itiden\Polywarp\Polywarp;

use function Orchestra\Testbench\workbench_path;

it('discovers all translations in folders', function (): void {
    $translations = app(Polywarp::class)->discoverTranslations();

    foreach (['en', 'sv'] as $lang) {
        expect($translations[$lang])
            ->toHaveKeys([
                'test.welcome',
                'test.goodbye',
                'test.greeting.morning',
                'test.greeting.afternoon',
                'test.greeting.evening',
                'test.farewell.formal',
                'test.farewell.informal',
            ]);
    }
});

it('discovers all used translation keys', function (): void {
    Config::set('polywarp.script_paths', [
        workbench_path('resources/js'),
    ]);

    $usedKeys = app(Polywarp::class)->discoverUsedTranslationKeys();

    expect($usedKeys->toArray())
        ->toEqualCanonicalizing([
            'test.welcome',
            'test-with-attributes',
        ]);
});

it('discovers used keys with attributes', function (): void {
    Config::set('polywarp.script_paths', [
        workbench_path('resources/js'),
    ]);

    $usedKeys = app(Polywarp::class)->discoverUsedTranslationKeys();
    $usedKeys = $usedKeys->toArray();

    expect($usedKeys)
        ->toEqualCanonicalizing([
            'test.welcome',
            'test-with-attributes',
        ]);
});

it('can compile', function (): void {
    $compiled = app(Polywarp::class)->compile(collect([
        'en' => [
            'foo' => 'bar',
        ],
        'sv' => [
            'foo' => 'bar',
        ],
    ]), collect());

    expect($compiled)
        ->toBe(<<<'TS'
        // This file is auto-generated. Do not edit it manually.
        /* eslint-disable */

        const translations = {"en":[],"sv":[]};

        type TranslationFunction = {
        (key: "foo"): string;
        };

        export const t: TranslationFunction = (
            key: string,
            params?: Record<string, string | number>
        ): string => {
            const lang = document.documentElement.lang as keyof typeof translations;

            const value = translations[lang][key as keyof (typeof translations)[typeof lang]];

            if (typeof value !== "string") {
                console.warn(`Translation key "${key}" not found`);
                return key;
            }

            if (!params) {
                return value;
            }

            return Object.entries(params).reduce(
                (str, [param, value]) => str.replace(`:${param}`, String(value)),
                String(value)
            );
        };

        TS);
});
