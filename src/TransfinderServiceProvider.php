<?php

declare(strict_types=1);

namespace Itiden\Transfinder;

use Illuminate\Support\ServiceProvider;
use Itiden\Transfinder\Console\Commands\GenerateTranslations;

final class TransfinderServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/transfinder.php', 'transfinder');
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
