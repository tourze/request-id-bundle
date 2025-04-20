<?php

namespace RequestIdBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\EventSubscriber\CommandRequestIdSubscriber;
use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;

class CommandRequestIdSubscriberTest extends TestCase
{
    private RequestIdStorage $storage;
    private RequestIdGenerator $generator;
    private CommandRequestIdSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->storage = new RequestIdStorage();

        /** @var RequestIdGenerator&\PHPUnit\Framework\MockObject\MockObject $generator */
        $generator = $this->createMock(RequestIdGenerator::class);
        $generator->expects($this->any())
            ->method('generate')
            ->willReturn('test-uuid');

        $this->generator = $generator;
        $this->subscriber = new CommandRequestIdSubscriber($this->storage, $this->generator);
    }

    public function testOnCommand(): void
    {
        $this->subscriber->onCommand();

        $requestId = $this->storage->getRequestId();
        $this->assertNotNull($requestId);
        $this->assertStringStartsWith('CLI', $requestId);
        $this->assertEquals('CLItest-uuid', $requestId);
    }

    public function testOnTerminate(): void
    {
        // 首先设置一个值
        $this->storage->setRequestId('CLItest-id');

        // 执行终止命令
        $this->subscriber->onTerminate();

        // 检查值已被清除
        $this->assertNull($this->storage->getRequestId());
    }
}
