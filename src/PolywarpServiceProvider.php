<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

use Illuminate\Support\ServiceProvider;
use Itiden\Polywarp\Console\Commands\GenerateTranslations;
use Itiden\Polywarp\Console\Commands\GetConfiguration;
use Override;

final class PolywarpServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/polywarp.php',
            key: 'polywarp',
        );

        $this->app->bindIf(
            abstract: TranslationDirectoriesResolver::class,
            concrete: GenericTranslationDirectoriesResolver::class,
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateTranslations::class,
                GetConfiguration::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/polywarp.php' => config_path(path: 'polywarp.php'),
            ], groups: 'polywarp-config');
        }
    }
}
