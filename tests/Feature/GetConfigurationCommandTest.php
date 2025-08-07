<?php

declare(strict_types=1);

use Itiden\Polywarp\Tests\TestCase;

it('returns the config in json format', function (): void {
    /** @var TestCase $this */

    $this->artisan('polywarp:config')
        ->expectsOutput(json_encode([
            'output_path' => config('polywarp.output_path'),
            'content_paths' => [
                '/Users/neo/workspace/packages/laravel-polywarp/workbench/resources/js/**/*.{ts,tsx,vue}',
            ],
            'translation_directories' => [
                '/Users/neo/workspace/packages/laravel-polywarp/vendor/laravel/framework/src/Illuminate/Translation/lang/**/*.{php,json}',
                '/Users/neo/workspace/packages/laravel-polywarp/workbench/lang/**/*.{php,json}'
            ]
        ]))
        ->assertExitCode(0);
});
