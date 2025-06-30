<?php

namespace RequestIdBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Service\ResetInterface;

/**
 * 在非 FPM 环境下，声明这个服务需要 reset
 */
#[AutoconfigureTag(name: 'as-coroutine')]
class RequestIdStorage implements ResetInterface
{
    private ?string $requestId = null;

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $id): void
    {
        $this->requestId = $id;
    }

    public function reset(): void
    {
        $this->requestId = null;
    }
}
