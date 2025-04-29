<?php

namespace Itiden\Transfinder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Itiden\Transfinder\Transfinder;

final class GenerateTranslations extends Command
{
    protected $signature = 'transfinder:generate';

    protected $description = 'Generate typescript translation files for the application';

    public function handle(Transfinder $transfinder): int
    {

        File::ensureDirectoryExists(pathinfo(config('transfinder.output_path'), PATHINFO_DIRNAME));
        File::put(
            config('transfinder.output_path'),
            $transfinder->compile(
                $transfinder->discoverTranslations(),
                $transfinder->discoverUsedTranslationKeys(),
            ),
        );

        $this->info('Translations generated successfully.');

        return Command::SUCCESS;
    }
}
