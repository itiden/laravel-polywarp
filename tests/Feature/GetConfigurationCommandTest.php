<?php

declare(strict_types=1);

use Itiden\Polywarp\Tests\TestCase;

use function Orchestra\Testbench\workbench_path;

it('returns the config in json format', function (): void {
    /** @var TestCase $this */

    $this->artisan('polywarp:config')
        ->expectsOutput(json_encode([
            'output_path' => config('polywarp.output_path'),
            'content_paths' => [
                workbench_path('/resources/js/**/*.{ts,tsx,vue}'),
            ],
            'translation_directories' => [
                realpath(workbench_path('../vendor/laravel/framework/src/Illuminate/Translation/lang')) . '/**/*.{php,json}',
                workbench_path('/lang/**/*.{php,json}'),
            ]
        ]))
        ->assertExitCode(0);
});
