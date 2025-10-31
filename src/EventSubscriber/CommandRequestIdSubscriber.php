<?php

declare(strict_types=1);

namespace RequestIdBundle\EventSubscriber;

use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Command命令，我们也生成一个requestId，方便我们跟踪
 */
class CommandRequestIdSubscriber
{
    public function __construct(
        private readonly RequestIdStorage $requestIdStorage,
        private readonly RequestIdGenerator $generator,
    ) {
    }

    #[AsEventListener(event: ConsoleEvents::COMMAND, priority: 999)]
    public function onCommand(): void
    {
        $this->requestIdStorage->setRequestId("CLI{$this->generator->generate()}");
    }

    #[AsEventListener(event: ConsoleEvents::TERMINATE, priority: -999)]
    public function onTerminate(): void
    {
        $this->requestIdStorage->setRequestId(null);
    }
}
