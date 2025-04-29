<?php

return [
    /**
     * The paths to search for translation files.
     * These paths will be searched recursively for translation files.
     */
    'lang_paths' => [
        lang_path()
    ],

    /**
     * The paths to scan for translation keys.
     */
    'script_paths' => [
        resource_path('js')
    ],

    /**
     * The path to the generated translation file.
     */
    'output_path' => resource_path('js/translations.ts'),
];
