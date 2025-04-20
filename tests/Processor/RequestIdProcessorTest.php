<?php

namespace RequestIdBundle\Tests\Processor;

use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use RequestIdBundle\Processor\RequestIdProcessor;
use RequestIdBundle\Service\RequestIdStorage;

class RequestIdProcessorTest extends TestCase
{
    private RequestIdStorage $storage;
    private RequestIdProcessor $processor;

    protected function setUp(): void
    {
        $this->storage = new RequestIdStorage();
        $this->processor = new RequestIdProcessor($this->storage);
    }

    public function testInvokeWithRequestId(): void
    {
        // 设置请求 ID
        $this->storage->setRequestId('test-request-id');

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            \Monolog\Level::Info,
            'message',
            [],
            []
        );

        // 调用处理器
        $processedRecord = $this->processor->__invoke($record);

        // 验证结果中包含请求 ID
        $this->assertArrayHasKey('request_id', $processedRecord->extra);
        $this->assertEquals('test-request-id', $processedRecord->extra['request_id']);
    }

    public function testInvokeWithoutRequestId(): void
    {
        // 不设置请求 ID
        $this->storage->setRequestId(null);

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            \Monolog\Level::Info,
            'message',
            [],
            []
        );

        // 调用处理器
        $processedRecord = $this->processor->__invoke($record);

        // 验证结果中不包含请求 ID
        $this->assertArrayNotHasKey('request_id', $processedRecord->extra);
    }
}
