<?php

namespace RequestIdBundle\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use RequestIdBundle\Service\RequestIdStorage;
use RequestIdBundle\Stamp\RequestIdStamp;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

/**
 * 定时任务时，传递requestId
 */
#[AutoconfigureTag(name: 'monolog.processor')]
#[AutoconfigureTag(name: 'as-coroutine')]
class MessengerProcessor implements ProcessorInterface, EventSubscriberInterface
{
    public function __construct(
        private readonly RequestIdStorage $requestIdStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => [
                ['onWorkerMessageReceived', 2048], // 越大，优先级越高
            ],
            WorkerRunningEvent::class => [
                ['onWorkerRunning', -2048], // 结束时尽可能晚处理
            ],
        ];
    }

    public function onWorkerMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        /** @var RequestIdStamp|null $requestIdStamp */
        $requestIdStamp = $event->getEnvelope()->last(RequestIdStamp::class);
        if ($requestIdStamp !== null) {
            $this->requestIdStorage->setRequestId($requestIdStamp->getRequestId());
        }
    }

    public function onWorkerRunning(): void
    {
        $this->requestIdStorage->setRequestId(null);
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}
