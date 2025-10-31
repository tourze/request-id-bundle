<?php

declare(strict_types=1);

namespace RequestIdBundle\EventSubscriber;

use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestIdEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestIdStorage $idStorage,
        private readonly RequestIdGenerator $idGenerator,
        private readonly string $requestHeader = 'Request-Id',
        private readonly string $responseHeader = 'Request-Id',
        private readonly bool $trustRequest = true,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => ['onResponse', -99],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $req = $event->getRequest();

        // always give the incoming request priority. If it has the ID in
        // its headers already put that into our ID storage.
        if ($this->trustRequest && ($id = $req->headers->get($this->requestHeader)) !== null && '' !== $id) {
            $this->idStorage->setRequestId($id);

            return;
        }

        // similarly, if the request ID storage already has an ID set we
        // don't need to do anything other than put it into the request headers
        if (($id = $this->idStorage->getRequestId()) !== null && '' !== $id) {
            $req->headers->set($this->requestHeader, $id);

            return;
        }

        $id = $this->idGenerator->generate();
        $req->headers->set($this->requestHeader, $id);
        $this->idStorage->setRequestId($id);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (($id = $this->idStorage->getRequestId()) !== null && '' !== $id) {
            $event->getResponse()->headers->set($this->responseHeader, $id);
        }
    }
}
