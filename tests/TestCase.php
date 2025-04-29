<?php

declare(strict_types=1);

namespace Itiden\Transfinder\Tests;

use Itiden\Transfinder\TransfinderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TransfinderServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // config()->set('database.default', 'testing');
    }
}
