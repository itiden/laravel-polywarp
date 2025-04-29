<?php

use Illuminate\Support\Facades\Config;
use Itiden\Transfinder\Transfinder;

use function Orchestra\Testbench\workbench_path;

it('discovers all translations in folders', function () {
    Config::set('transfinder.lang_paths', [
        workbench_path('lang'),
    ]);

    $translations = app(Transfinder::class)->discoverTranslations();

    foreach (['en', 'sv'] as $lang) {
        expect($translations[$lang]['test'])->toHaveKeys([
            'welcome',
            'goodbye',
            'greeting.morning',
            'greeting.afternoon',
            'greeting.evening',
            'farewell.formal',
            'farewell.informal',
        ]);
    }
});

it('discovers all used translation keys', function () {
    Config::set('transfinder.script_paths', [
        workbench_path('resources/js'),
    ]);

    $usedKeys = app(Transfinder::class)->discoverUsedTranslationKeys();

    expect($usedKeys->toArray())->toEqualCanonicalizing([
        'test.welcome',
        'test-with-attributes',
    ]);
});

it('discovers used keys with attributes', function () {
    Config::set('transfinder.script_paths', [
        workbench_path('resources/js'),
    ]);

    $usedKeys = app(Transfinder::class)->discoverUsedTranslationKeys();
    $usedKeys = $usedKeys->toArray();

    expect($usedKeys)->toEqualCanonicalizing([
        'test.welcome',
        'test-with-attributes',
    ]);
});

it('can compile', function () {
    Config::set('transfinder.lang_paths', [
        workbench_path('lang'),
    ]);

    Config::set('transfinder.script_paths', [
        workbench_path('resources/js'),
    ]);

    $translations = app(Transfinder::class)->discoverTranslations();
    $usedKeys = app(Transfinder::class)->discoverUsedTranslationKeys();
    $compiled = app(Transfinder::class)->compile($translations, $usedKeys);


    expect($compiled)->toBe(
        <<<'TS'
        // This file is auto-generated. Do not edit it manually.
        /* eslint-disable */

        const translations = {"en":[],"sv":[]};

        type TranslationFunction = {
        (key: "test.welcome"): string;(key: "test.goodbye"): string;(key: "test.greeting.morning"): string;(key: "test.greeting.afternoon"): string;(key: "test.greeting.evening"): string;(key: "test.farewell.formal"): string;(key: "test.farewell.informal"): string;
        };

        export const t: TranslationFunction = (
            key: string,
            params?: Record<string, string | number>
        ): string => {
            const lang = document.documentElement.lang as keyof typeof translations;

            const value = translations[lang][key];

            if (typeof value !== "string") {
                console.warn(`Translation key "${key}" not found`);
                return key;
            }

            if (!params) {
                return value;
            }

            return Object.entries(params).reduce(
                (str, [param, value]) => str.replace(`:${param}`, String(value)),
                value
            );
        };

        TS
    );
});
