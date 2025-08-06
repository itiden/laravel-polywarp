<?php

declare(strict_types=1);

it('returns a successful response', function (): void {
    @unlink(config('polywarp.output_path'));

    $this->artisan('polywarp:generate')->expectsOutput('Translations generated successfully.')->assertExitCode(0);

    expect(file_exists(config('polywarp.output_path')))->toBeTrue();
});
