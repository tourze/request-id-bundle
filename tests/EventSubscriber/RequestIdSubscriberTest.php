<?php

namespace RequestIdBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\EventSubscriber\RequestIdSubscriber;
use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestIdSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = RequestIdSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
        $this->assertEquals(['onRequest', 100], $events[KernelEvents::REQUEST]);
        $this->assertEquals(['onResponse', -99], $events[KernelEvents::RESPONSE]);
    }

    /**
     * 测试 RequestIdSubscriber 的构造函数功能
     */
    public function testConstructor(): void
    {
        $idStorage = new RequestIdStorage();
        /** @var RequestIdGenerator&\PHPUnit\Framework\MockObject\MockObject $idGenerator */
        $idGenerator = $this->createMock(RequestIdGenerator::class);

        $subscriber = new RequestIdSubscriber(
            $idStorage,
            $idGenerator,
            'Custom-Request-Header',
            'Custom-Response-Header',
            false
        );

        // 使用反射访问私有属性
        $reflectionObject = new \ReflectionObject($subscriber);

        $requestHeaderProperty = $reflectionObject->getProperty('requestHeader');
        $requestHeaderProperty->setAccessible(true);
        $this->assertEquals('Custom-Request-Header', $requestHeaderProperty->getValue($subscriber));

        $responseHeaderProperty = $reflectionObject->getProperty('responseHeader');
        $responseHeaderProperty->setAccessible(true);
        $this->assertEquals('Custom-Response-Header', $responseHeaderProperty->getValue($subscriber));

        $trustRequestProperty = $reflectionObject->getProperty('trustRequest');
        $trustRequestProperty->setAccessible(true);
        $this->assertFalse($trustRequestProperty->getValue($subscriber));
    }

    /**
     * 测试默认构造函数参数
     */
    public function testDefaultConstructorParameters(): void
    {
        $idStorage = new RequestIdStorage();
        /** @var RequestIdGenerator&\PHPUnit\Framework\MockObject\MockObject $idGenerator */
        $idGenerator = $this->createMock(RequestIdGenerator::class);

        $subscriber = new RequestIdSubscriber($idStorage, $idGenerator);

        // 使用反射访问私有属性
        $reflectionObject = new \ReflectionObject($subscriber);

        $requestHeaderProperty = $reflectionObject->getProperty('requestHeader');
        $requestHeaderProperty->setAccessible(true);
        $this->assertEquals('Request-Id', $requestHeaderProperty->getValue($subscriber));

        $responseHeaderProperty = $reflectionObject->getProperty('responseHeader');
        $responseHeaderProperty->setAccessible(true);
        $this->assertEquals('Request-Id', $responseHeaderProperty->getValue($subscriber));

        $trustRequestProperty = $reflectionObject->getProperty('trustRequest');
        $trustRequestProperty->setAccessible(true);
        $this->assertTrue($trustRequestProperty->getValue($subscriber));
    }
}
