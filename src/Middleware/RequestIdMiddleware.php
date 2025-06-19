<?php

namespace RequestIdBundle\Middleware;

use RequestIdBundle\Service\RequestIdStorage;
use RequestIdBundle\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

/**
 * 有时候，一个请求会产生N个消息，然后因为消息是异步执行的，我们就不好查对应请求的消息是否按照预期执行。
 * 为此我们添加一个中间件来记录生产消息时的requestID
 */
class RequestIdMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RequestIdStorage $requestIdStorage)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) === null || ($requestIdStamp = $envelope->last(RequestIdStamp::class)) === null) {
            if (!empty($this->requestIdStorage->getRequestId())) {
                $envelope = $envelope->with(new RequestIdStamp($this->requestIdStorage->getRequestId()));
            }

            return $stack->next()->handle($envelope, $stack);
        }

        /* @var RequestIdStamp $requestIdStamp */
        $this->requestIdStorage->setRequestId($requestIdStamp->getRequestId());

        return $stack->next()->handle($envelope, $stack);
    }
}
