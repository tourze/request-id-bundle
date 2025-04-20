<?php

namespace RequestIdBundle\Tests\Stamp;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\Stamp\RequestIdStamp;

class RequestIdStampTest extends TestCase
{
    public function testConstructorAndGetter(): void
    {
        $requestId = 'test-stamp-id';
        $stamp = new RequestIdStamp($requestId);

        $this->assertEquals($requestId, $stamp->getRequestId());
    }
}
