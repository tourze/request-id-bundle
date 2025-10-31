<?php

declare(strict_types=1);

namespace RequestIdBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use RequestIdBundle\Middleware\RequestIdMiddleware;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\BundleDependency\BundleDependencyInterface;

class RequestIdBundle extends Bundle implements BundleDependencyInterface
{
    public function boot(): void
    {
        parent::boot();
        $fileName = (new \ReflectionClass(RequestIdMiddleware::class))->getFileName();
        if (false !== $fileName) {
            Backtrace::addProdIgnoreFiles($fileName);
        }
    }

    public static function getBundleDependencies(): array
    {
        return [
            FrameworkBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            MonologBundle::class => ['all' => true],
        ];
    }
}
