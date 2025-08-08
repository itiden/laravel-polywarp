<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

interface TranslationDirectoriesResolver
{
    /**
     * Get all translation directories polywarp should scan and include in the types.
     *
     * @return string[]
     */
    public function resolve(): array;
}
