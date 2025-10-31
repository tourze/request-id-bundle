<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\EventSubscriber\CommandRequestIdSubscriber;
use RequestIdBundle\Service\RequestIdStorage;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(CommandRequestIdSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class CommandRequestIdSubscriberTest extends AbstractEventSubscriberTestCase
{
    private RequestIdStorage $storage;

    // private RequestIdGenerator $generator; // 不再需要，在集成测试中直接从容器获取

    private CommandRequestIdSubscriber $subscriber;

    protected function onSetUp(): void
    {
        // 在集成测试中，我们从容器获取服务实例
        $this->storage = self::getService(RequestIdStorage::class);
        $this->subscriber = self::getService(CommandRequestIdSubscriber::class);
    }

    public function testOnCommand(): void
    {
        // 清理可能存在的旧数据
        $this->storage->setRequestId(null);

        $this->subscriber->onCommand();

        $requestId = $this->storage->getRequestId();
        $this->assertIsString($requestId);
        $this->assertStringStartsWith('CLI', $requestId);
        // 验证ID格式正确（CLI + Base58 UUID）
        $this->assertMatchesRegularExpression('/^CLI[1-9A-HJ-NP-Za-km-z]+$/', $requestId);
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
