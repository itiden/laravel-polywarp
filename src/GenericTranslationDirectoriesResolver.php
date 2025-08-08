<?php

declare(strict_types=1);

namespace Itiden\Polywarp;

use Override;

final readonly class GenericTranslationDirectoriesResolver implements TranslationDirectoriesResolver
{
    #[Override]
    public function resolve(): array
    {
        return app(abstract: 'translator')->getLoader()->paths();
    }
}
