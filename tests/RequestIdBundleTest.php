<?php

namespace RequestIdBundle\Tests;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\RequestIdBundle;
use Tourze\BacktraceHelper\Backtrace;

class RequestIdBundleTest extends TestCase
{
    public function testBoot(): void
    {
        // 由于 boot 方法调用了静态方法，我们需要使用反射来访问被忽略文件列表
        // 首先保存当前的忽略文件列表
        $reflection = new \ReflectionProperty(Backtrace::class, 'prodIgnoreFiles');
        $reflection->setAccessible(true);
        $originalIgnoreFiles = $reflection->getValue(null);

        try {
            // 重置忽略文件列表
            $reflection->setValue(null, []);

            // 执行 boot 方法
            $bundle = new RequestIdBundle();
            $bundle->boot();

            // 获取更新后的忽略文件列表
            $updatedIgnoreFiles = $reflection->getValue(null);

            // 验证中间件文件已被添加到忽略列表中
            $this->assertNotEmpty($updatedIgnoreFiles);
            $middlewareFile = (new \ReflectionClass(\RequestIdBundle\Middleware\RequestIdMiddleware::class))->getFileName();
            $this->assertContains($middlewareFile, $updatedIgnoreFiles);
        } finally {
            // 恢复原始的忽略文件列表
            $reflection->setValue(null, $originalIgnoreFiles);
        }
    }
}
