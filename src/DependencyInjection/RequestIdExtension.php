<?php

declare(strict_types=1);

namespace RequestIdBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class RequestIdExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
