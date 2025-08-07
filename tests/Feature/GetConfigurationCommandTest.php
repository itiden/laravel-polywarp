<?php

declare(strict_types=1);

use Itiden\Polywarp\Tests\TestCase;

it('returns the config in json format', function (): void {
    /** @var TestCase $this */


    $command = $this->artisan('polywarp:config')
        ->expectsOutput(json_encode([
            'output_path' => config('polywarp.output_path'),
            'content_paths' => config('polywarp.content_paths'),
            'translation_directories' => app('translator')->getLoader()->paths()
        ]))
        ->assertExitCode(0);
});
