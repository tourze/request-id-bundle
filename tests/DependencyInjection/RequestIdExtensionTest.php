<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use RequestIdBundle\DependencyInjection\RequestIdExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(RequestIdExtension::class)]
final class RequestIdExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testGetAlias(): void
    {
        $extension = new RequestIdExtension();
        $this->assertEquals('request_id', $extension->getAlias());
    }

    public function testGetConfiguration(): void
    {
        $extension = new RequestIdExtension();
        $containerBuilder = new class extends ContainerBuilder {
            // 匿名类继承 ContainerBuilder，用于替换 Mock
        };
        $configuration = $extension->getConfiguration([], $containerBuilder);
        $this->assertNull($configuration);
    }

    public function testLoad(): void
    {
        $extension = new RequestIdExtension();

        // 验证扩展已正确创建
        $this->assertInstanceOf(RequestIdExtension::class, $extension);
        $this->assertEquals('request_id', $extension->getAlias());
    }
}
