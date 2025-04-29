<?php

declare(strict_types=1);

namespace Itiden\Transfinder\Tests;

use Itiden\Transfinder\TransfinderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

use function Orchestra\Testbench\package_path;

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

    public static function applicationBasePath()
    {
        return package_path('workbench');
    }
}
