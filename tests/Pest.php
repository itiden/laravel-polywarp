<?php

declare(strict_types=1);

use Itiden\Transfinder\Tests\TestCase;

pest()->extend(TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');
