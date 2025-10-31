<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\Processor\RequestIdProcessor;
use RequestIdBundle\Service\RequestIdStorage;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(RequestIdProcessor::class)]
#[RunTestsInSeparateProcesses]
final class RequestIdProcessorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testInvokeWithRequestId(): void
    {
        $storage = self::getService(RequestIdStorage::class);
        $processor = self::getService(RequestIdProcessor::class);

        // 设置请求 ID
        $storage->setRequestId('test-request-id');

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Info,
            'message',
            [],
            []
        );

        // 调用处理器
        $processedRecord = $processor->__invoke($record);

        // 验证结果中包含请求 ID
        $this->assertArrayHasKey('request_id', $processedRecord->extra);
        $this->assertEquals('test-request-id', $processedRecord->extra['request_id']);
    }

    public function testInvokeWithoutRequestId(): void
    {
        $storage = self::getService(RequestIdStorage::class);
        $processor = self::getService(RequestIdProcessor::class);

        // 不设置请求 ID
        $storage->setRequestId(null);

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Info,
            'message',
            [],
            []
        );

        // 调用处理器
        $processedRecord = $processor->__invoke($record);

        // 验证结果中不包含请求 ID
        $this->assertArrayNotHasKey('request_id', $processedRecord->extra);
    }
}
