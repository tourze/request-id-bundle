<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Stamp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RequestIdBundle\Stamp\RequestIdStamp;

/**
 * @internal
 */
#[CoversClass(RequestIdStamp::class)]
final class RequestIdStampTest extends TestCase
{
    public function testConstructorAndGetter(): void
    {
        $requestId = 'test-stamp-id';
        $stamp = new RequestIdStamp($requestId);

        $this->assertEquals($requestId, $stamp->getRequestId());
    }
}
