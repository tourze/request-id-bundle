<?php

namespace RequestIdBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\Service\RequestIdGenerator;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\UuidV4;

class RequestIdGeneratorTest extends TestCase
{
    public function testGenerateCreatesBase58EncodedUuid(): void
    {
        $uuid = $this->createMock(UuidV4::class);
        $uuid->expects($this->once())
            ->method('toBase58')
            ->willReturn('2ejeyxwdJJ9cLQSiuJf3Z4');

        /** @var UuidFactory&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(UuidFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->willReturn($uuid);

        $generator = new RequestIdGenerator($factory);
        $result = $generator->generate();

        $this->assertEquals('2ejeyxwdJJ9cLQSiuJf3Z4', $result);
    }
}
