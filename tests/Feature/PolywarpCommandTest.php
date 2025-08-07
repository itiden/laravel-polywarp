<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Itiden\Polywarp\Console\Commands\GenerateTranslations;
use Itiden\Polywarp\Polywarp;

covers(GenerateTranslations::class);

it('returns a successful response', function (): void {
    @unlink(config('polywarp.output_path'));

    $this->artisan('polywarp:generate')->expectsOutput('Translations generated successfully.')->assertExitCode(0);

    expect(file_exists(config('polywarp.output_path')))->toBeTrue();
});

it('caches translations', function (): void {
    $this->artisan('polywarp:generate')->assertExitCode(0);

    $cache = Cache::store(name: 'file');

    expect($cache->has(key: 'polywarp.available_translations'))->toBeTrue();
    expect($cache->has(key: 'polywarp.used_translation_keys'))->toBeTrue();
});

it('validates input options', function (): void {
    $this->artisan('polywarp:generate', ['--use-cache-for' => 'invalid-option'])
        ->assertExitCode(1);
})->throws(ValidationException::class);

it('uses populates and refreshes the cache correctly', function (): void {
    // Fake having fewer translations here
    $translations = app(Polywarp::class)->discoverTranslations()->forget('en');

    $store = Cache::store(name: 'file');

    // Simulate we the user has added a bunch of translations and make sure the "available translations" cache is recalculated
    $store->put(key: 'polywarp.available_translations', value: $translations);

    $this->artisan('polywarp:generate', ['--use-cache-for' => 'used-translations'])
        ->assertExitCode(0);

    expect($store->has(key: 'polywarp.available_translations'))->toBeTrue();
    expect($store->get(key: 'polywarp.available_translations')->toArray())
        ->toEqualCanonicalizing(app(Polywarp::class)->discoverTranslations()->toArray());


    // Simulate we the user has added a bunch of used translation keys and that the "available translations" cache isn't refreshed
    $store->put(key: 'polywarp.available_translations', value: $translations);

    $this->artisan('polywarp:generate', ['--use-cache-for' => 'available-translations'])
        ->assertExitCode(0);

    expect($store->has(key: 'polywarp.available_translations'))->toBeTrue();
    expect($store->get(key: 'polywarp.available_translations')->toArray())
        ->toEqualCanonicalizing($translations->toArray());
});
