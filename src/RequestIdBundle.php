<?php

namespace RequestIdBundle;

use RequestIdBundle\Middleware\RequestIdMiddleware;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;

class RequestIdBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(RequestIdMiddleware::class))->getFileName());
    }
}
