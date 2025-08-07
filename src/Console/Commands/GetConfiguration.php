<?php

declare(strict_types=1);

namespace Itiden\Polywarp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

use function Illuminate\Filesystem\join_paths;

final class GetConfiguration extends Command
{
    protected $signature = 'polywarp:config';

    protected $description = 'Get the polywarp configuration in JSON format, used by the vite plugin';

    public function handle(): int
    {
        $this->line(json_encode([
            'output_path' => Config::string(key: 'polywarp.output_path'),
            'content_paths' => static::buildGlobPattern(
                paths: Config::array(key: 'polywarp.content_paths'),
                extensions: Config::array(key: 'polywarp.extenstion_to_scan'),
            ),
            'translation_directories' => static::buildGlobPattern(
                paths: app('translator')->getLoader()->paths(),
                extensions: [
                    'php',
                    'json',
                ],
            ),
        ]));

        return Command::SUCCESS;
    }

    /**
     * @param string[] $paths
     * @param string[] $extensions
     * @return string[]
     */
    private static function buildGlobPattern(array $paths, array $extensions): array
    {
        return array_map(fn(string $path): string => join_paths($path, '/**/*.{' . implode(
            separator: ',',
            array: $extensions,
        ) . '}'), $paths);
    }
}
