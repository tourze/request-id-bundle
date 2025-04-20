<?php

namespace RequestIdBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\Service\RequestIdStorage;

class RequestIdStorageTest extends TestCase
{
    public function testGetAndSetRequestId(): void
    {
        $storage = new RequestIdStorage();

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
        $storage = new RequestIdStorage();
        $storage->setRequestId('test-id');

        // 重置后应该恢复为 null
        $storage->reset();
        $this->assertNull($storage->getRequestId());
    }
}
