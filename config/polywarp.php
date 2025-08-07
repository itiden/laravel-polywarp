<?php

return [
    /**
     * The paths to scan for translation keys.
     */
    'content_paths' => [
        resource_path('js')
    ],

    /**
     * File extenstions to scan for translation keys.
     */
    'extenstion_to_scan' => [
        'ts',
        'tsx',
        'vue',
    ],

    /**
     * The path to the generated translation file.
     */
    'output_path' => resource_path('js/translations.ts'),
];
