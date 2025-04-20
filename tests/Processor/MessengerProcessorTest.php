<?php

namespace RequestIdBundle\Tests\Processor;

use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use RequestIdBundle\Processor\MessengerProcessor;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

class MessengerProcessorTest extends TestCase
{
    private RequestIdStorage $storage;
    private MessengerProcessor $processor;

    protected function setUp(): void
    {
        $this->storage = new RequestIdStorage();
        $this->processor = new MessengerProcessor($this->storage);
    }

    public function testInvoke(): void
    {
        // 测试 __invoke 方法，它应该原样返回记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            \Monolog\Level::Info,
            'message',
            [],
            []
        );

        $result = $this->processor->__invoke($record);

        // 验证结果就是原始记录
        $this->assertSame($record, $result);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = MessengerProcessor::getSubscribedEvents();

        // 验证事件订阅
        $this->assertArrayHasKey(WorkerMessageReceivedEvent::class, $events);
        $this->assertArrayHasKey(WorkerRunningEvent::class, $events);

        // 验证优先级
        $this->assertEquals([['onWorkerMessageReceived', 2048]], $events[WorkerMessageReceivedEvent::class]);
        $this->assertEquals([['onWorkerRunning', -2048]], $events[WorkerRunningEvent::class]);
    }

    /**
     * 测试构造函数和对处理器实例的依赖注入
     */
    public function testConstructorAndDependencyInjection(): void
    {
        $storage = new RequestIdStorage();
        $processor = new MessengerProcessor($storage);

        // 使用反射检查依赖注入
        $reflectionObject = new \ReflectionObject($processor);
        $requestIdStorageProperty = $reflectionObject->getProperty('requestIdStorage');
        $requestIdStorageProperty->setAccessible(true);

        $this->assertSame($storage, $requestIdStorageProperty->getValue($processor));
    }

    /**
     * 测试处理器实现了正确的接口
     */
    public function testImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf(\Monolog\Processor\ProcessorInterface::class, $this->processor);
        $this->assertInstanceOf(\Symfony\Component\EventDispatcher\EventSubscriberInterface::class, $this->processor);
    }
}
