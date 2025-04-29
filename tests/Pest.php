<?php

declare(strict_types=1);

use Itiden\Polywarp\Tests\TestCase;

pest()
    ->extend(TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

pest()->group('feature')->in('Feature');
pest()->group('unit')->in('Unit');
