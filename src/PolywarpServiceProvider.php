<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

use Illuminate\Support\ServiceProvider;
use Itiden\Polywarp\Console\Commands\GenerateTranslations;
use Override;

final class PolywarpServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/polywarp.php', 'polywarp');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateTranslations::class,
            ]);
        }
    }
}
