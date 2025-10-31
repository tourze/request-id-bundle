<?php

declare(strict_types=1);

namespace RequestIdBundle\Service;

use Symfony\Component\Uid\Factory\UuidFactory;

/**
 * 跟原版不同的是，我们这里生成的是 Base58 会短一点
 */
class RequestIdGenerator
{
    public function __construct(
        private readonly UuidFactory $factory,
    ) {
    }

    public function generate(): string
    {
        return $this->factory->create()->toBase58();
    }
}
