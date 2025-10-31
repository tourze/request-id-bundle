<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\Service\RequestIdStorage;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(RequestIdStorage::class)]
#[RunTestsInSeparateProcesses]
final class RequestIdStorageTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testGetAndSetRequestId(): void
    {
        $storage = self::getService(RequestIdStorage::class);

        // 初始状态应该是 null
        $this->assertNull($storage->getRequestId());

        // 设置后应该能正确获取
        $storage->setRequestId('test-id');
        $this->assertEquals('test-id', $storage->getRequestId());

        // 设置为 null 应该恢复初始状态
        $storage->setRequestId(null);
        $this->assertNull($storage->getRequestId());
    }

    public function testReset(): void
    {
        $storage = self::getService(RequestIdStorage::class);
        $storage->setRequestId('test-id');

        // 重置后应该恢复为 null
        $storage->reset();
        $this->assertNull($storage->getRequestId());
    }
}
