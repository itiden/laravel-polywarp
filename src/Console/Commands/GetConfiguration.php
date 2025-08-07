<?php

declare(strict_types=1);

namespace Itiden\Polywarp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

final class GetConfiguration extends Command
{
    protected $signature = 'polywarp:config';

    protected $description = 'Get the polywarp configuration in JSON format, used by the vite plugin';

    public function handle(): int
    {
        $this->line(json_encode([
            'output_path' => Config::string(key: 'polywarp.output_path'),
            'content_paths' => Config::array(key: 'polywarp.content_paths'),
            'translation_directories' => app('translator')->getLoader()->paths(),
        ]));

        return Command::SUCCESS;
    }
}
