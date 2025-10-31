<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RequestIdBundle\Service\RequestIdGenerator;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\UuidV4;

/**
 * @internal
 */
#[CoversClass(RequestIdGenerator::class)]
final class RequestIdGeneratorTest extends TestCase
{
    public function testGenerateCreatesBase58EncodedUuid(): void
    {
        // 创建匿名类替换 UuidV4 Mock，返回固定的 Base58 编码值
        $uuid = new class extends UuidV4 {
            public function __construct()
            {
                // 调用父类构造函数，传递有效的 UUIDv4 字符串
                parent::__construct('123e4567-e89b-42d3-a456-426614174000');
            }

            public function toBase58(): string
            {
                return '2ejeyxwdJJ9cLQSiuJf3Z4';
            }
        };

        // 创建匿名类替换 UuidFactory Mock，返回上面创建的 UUID 实例
        $factory = new class($uuid) extends UuidFactory {
            private UuidV4 $uuid;

            public function __construct(UuidV4 $uuid)
            {
                // 调用父类构造函数初始化工厂
                parent::__construct();
                $this->uuid = $uuid;
            }

            public function create(): UuidV4
            {
                return $this->uuid;
            }
        };

        $generator = new RequestIdGenerator($factory);
        $result = $generator->generate();

        $this->assertEquals('2ejeyxwdJJ9cLQSiuJf3Z4', $result);
    }
}
