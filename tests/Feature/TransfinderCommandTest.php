<?php

it('returns a successful response', function () {
    $this->artisan('transfinder:generate')
        ->expectsOutput('Translations generated successfully.')
        ->assertExitCode(0);
})->todo();
