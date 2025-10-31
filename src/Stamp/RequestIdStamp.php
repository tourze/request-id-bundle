<?php

declare(strict_types=1);

namespace RequestIdBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class RequestIdStamp implements StampInterface
{
    public function __construct(private readonly string $requestId)
    {
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
