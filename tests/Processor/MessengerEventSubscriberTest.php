<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\Processor\MessengerEventSubscriber;
use RequestIdBundle\Service\RequestIdStorage;
use RequestIdBundle\Stamp\RequestIdStamp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(MessengerEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class MessengerEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    private RequestIdStorage $storage;

    private MessengerEventSubscriber $processor;

    protected function onSetUp(): void
    {
        // 在集成测试中，我们从容器获取服务实例
        $this->storage = self::getService(RequestIdStorage::class);
        $this->processor = self::getService(MessengerEventSubscriber::class);
    }

    public function testInvoke(): void
    {
        // 测试 __invoke 方法，它应该原样返回记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Info,
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
        $events = MessengerEventSubscriber::getSubscribedEvents();

        // 验证事件订阅
        $this->assertArrayHasKey(WorkerMessageReceivedEvent::class, $events);
        $this->assertArrayHasKey(WorkerRunningEvent::class, $events);

        // 验证优先级
        $this->assertEquals([['onWorkerMessageReceived', 2048]], $events[WorkerMessageReceivedEvent::class]);
        $this->assertEquals([['onWorkerRunning', -2048]], $events[WorkerRunningEvent::class]);
    }

    /**
     * 测试依赖注入
     */
    public function testDependencyInjection(): void
    {
        // 在集成测试中，我们验证服务已正确配置
        $this->assertInstanceOf(RequestIdStorage::class, $this->storage);
        $this->assertInstanceOf(MessengerEventSubscriber::class, $this->processor);
    }

    /**
     * 测试处理器实现了正确的接口
     */
    public function testImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf(ProcessorInterface::class, $this->processor);
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->processor);
    }

    public function testOnWorkerMessageReceived(): void
    {
        $requestIdStamp = new RequestIdStamp('test-message-id');
        $envelope = new Envelope(new \stdClass(), [$requestIdStamp]);

        $event = new WorkerMessageReceivedEvent($envelope, 'transport');

        $this->processor->onWorkerMessageReceived($event);

        $this->assertEquals('test-message-id', $this->storage->getRequestId());
    }

    public function testOnWorkerMessageReceivedWithoutStamp(): void
    {
        $envelope = new Envelope(new \stdClass(), []);

        $event = new WorkerMessageReceivedEvent($envelope, 'transport');

        $this->storage->setRequestId('existing-id');
        $this->processor->onWorkerMessageReceived($event);

        $this->assertEquals('existing-id', $this->storage->getRequestId());
    }

    public function testOnWorkerRunning(): void
    {
        $this->storage->setRequestId('test-id');
        $this->assertEquals('test-id', $this->storage->getRequestId());

        $this->processor->onWorkerRunning();

        $this->assertNull($this->storage->getRequestId());
    }
}
