<?php

declare(strict_types=1);

namespace Itiden\Polywarp\Tests;

use Itiden\Polywarp\PolywarpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

use function Orchestra\Testbench\package_path;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PolywarpServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // config()->set('database.default', 'testing');
    }

    public static function applicationBasePath(): string
    {
        return package_path(path: 'workbench');
    }
}
