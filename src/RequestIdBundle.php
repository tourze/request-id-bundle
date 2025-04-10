<?php

namespace RequestIdBundle;

use RequestIdBundle\Middleware\RequestIdMiddleware;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\BundleDependency\BundleDependencyInterface;

class RequestIdBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
        ];
    }

    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(RequestIdMiddleware::class))->getFileName());
    }
}
