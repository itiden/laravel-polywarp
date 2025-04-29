<?php

declare(strict_types=1);

namespace Itiden\Transfinder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Itiden\Transfinder\Transfinder;

final class GenerateTranslations extends Command
{
    protected $signature = 'transfinder:generate';

    protected $description = 'Generate typescript translation files for the application';

    public function handle(Transfinder $transfinder): int
    {
        $outFile = Config::get('transfinder.output_path');

        File::ensureDirectoryExists(pathinfo($outFile, PATHINFO_DIRNAME));

        File::put(
            $outFile,
            $transfinder->compile(
                $transfinder->discoverTranslations(),
                $transfinder->discoverUsedTranslationKeys()
            ),
        );

        $this->info('Translations generated successfully.');

        return Command::SUCCESS;
    }
}
