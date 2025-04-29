<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('returns a successful response', function (): void {
    @unlink(config('transfinder.output_path'));

    $this->artisan('transfinder:generate')->expectsOutput('Translations generated successfully.')->assertExitCode(0);

    expect(file_exists(config('transfinder.output_path')))->toBeTrue();
});
