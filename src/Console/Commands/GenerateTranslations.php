<?php

declare(strict_types=1);

namespace Itiden\Polywarp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Itiden\Polywarp\Polywarp;

final class GenerateTranslations extends Command
{
    protected $signature = 'polywarp:generate';

    protected $description = 'Generate typescript translation files for the application';

    public function handle(Polywarp $polywarp): int
    {
        $outFile = Config::get(key: 'polywarp.output_path');

        File::ensureDirectoryExists(pathinfo($outFile, PATHINFO_DIRNAME));

        File::put($outFile, $polywarp->compile(
            availableTranlsations: $polywarp->discoverTranslations(),
            keysToKeep: $polywarp->discoverUsedTranslationKeys(),
        ));

        $this->info(string: 'Translations generated successfully.');

        return Command::SUCCESS;
    }
}
