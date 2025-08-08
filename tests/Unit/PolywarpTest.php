<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Itiden\Polywarp\Polywarp;

use function Orchestra\Testbench\workbench_path;

covers(Polywarp::class);

it('discovers all translations in folders', function (): void {
    $translations = app(Polywarp::class)->discoverTranslations();

    expect($translations)->toHaveKeys(['en', 'sv']);

    foreach (['en', 'sv'] as $lang) {
        expect($translations[$lang])->toHaveKeys([
            'hello',
            'world',
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
    Config::set(key: 'polywarp.script_paths', value: [
        workbench_path(path: 'resources/js'),
    ]);

    $usedKeys = app(Polywarp::class)->discoverUsedTranslationKeys();

    expect($usedKeys->toArray())->toEqualCanonicalizing([
        'test.welcome',
        'test-with-attributes',
    ]);
});

it('discovers used keys with attributes', function (): void {
    Config::set(key: 'polywarp.script_paths', value: [
        workbench_path(path: 'resources/js'),
    ]);

    $usedKeys = app(Polywarp::class)->discoverUsedTranslationKeys();
    $usedKeys = $usedKeys->toArray();

    expect($usedKeys)->toEqualCanonicalizing([
        'test.welcome',
        'test-with-attributes',
    ]);
});

it('can compile', function (): void {
    $compiled = app(Polywarp::class)
        ->compile(collect([
            'en' => [
                'foo' => 'bar',
                'baz' => 'qux :var',
                'i_have_many_attributes' => ':duplicate :duplicate :duplicate cool :another',
            ],
            'sv' => [
                'foo' => 'bar',
                'baz' => 'qux :var',
                'i_have_many_attributes' => ':duplicate :duplicate :duplicate cool (på svenska) :another'
            ],
        ]), collect([
            'foo',
        ]));

    expect($compiled)
        ->toBe(<<<'TS'
        // This file is auto-generated. Do not edit it manually.
        /* eslint-disable */

        const translations = {"en":{"foo":"bar"},"sv":{"foo":"bar"}};

        type TranslationFunction = {
            (key: `foo`): string;(key: `baz`, params: { var: string | number }): string;(key: `i_have_many_attributes`, params: { duplicate: string | number, another: string | number }): string;
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
