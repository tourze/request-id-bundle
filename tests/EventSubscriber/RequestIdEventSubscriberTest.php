<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\EventSubscriber\RequestIdEventSubscriber;
use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(RequestIdEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class RequestIdEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 该测试类不需要额外的设置
    }

    public function testGetSubscribedEvents(): void
    {
        $events = RequestIdEventSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
        $this->assertEquals(['onRequest', 100], $events[KernelEvents::REQUEST]);
        $this->assertEquals(['onResponse', -99], $events[KernelEvents::RESPONSE]);
    }

    /**
     * 测试 RequestIdEventSubscriber 的构造函数功能
     */
    public function testConstructor(): void
    {
        // 在集成测试中，我们从容器获取服务实例
        $subscriber = self::getService(RequestIdEventSubscriber::class);

        // 验证服务已正确初始化
        $this->assertInstanceOf(RequestIdEventSubscriber::class, $subscriber);
    }

    /**
     * 测试默认配置
     */
    public function testDefaultConfiguration(): void
    {
        // 在集成测试中，我们验证服务的默认配置通过容器正确设置
        $storage = self::getService(RequestIdStorage::class);
        $generator = self::getService(RequestIdGenerator::class);

        // 验证依赖服务已正确注入
        $this->assertInstanceOf(RequestIdStorage::class, $storage);
        $this->assertInstanceOf(RequestIdGenerator::class, $generator);
    }

    public function testOnRequest(): void
    {
        // 在集成测试中，我们使用真实的容器服务
        $subscriber = self::getService(RequestIdEventSubscriber::class);
        $storage = self::getService(RequestIdStorage::class);

        // 清理可能存在的旧数据
        $storage->setRequestId(null);

        $request = new Request();
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onRequest($event);

        // 验证请求ID已生成并设置
        $requestId = $storage->getRequestId();
        $this->assertIsString($requestId);
        $this->assertStringStartsWith('1', $requestId); // Base58 UUID 通常以 1 开头
        $this->assertEquals($requestId, $request->headers->get('Request-Id'));
    }

    public function testOnRequestWithExistingHeader(): void
    {
        // 在集成测试中，我们使用真实的容器服务
        $subscriber = self::getService(RequestIdEventSubscriber::class);
        $storage = self::getService(RequestIdStorage::class);

        // 清理可能存在的旧数据
        $storage->setRequestId(null);

        $request = new Request();
        $request->headers->set('Request-Id', 'existing-id');
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onRequest($event);

        // 验证使用了现有的请求ID
        $this->assertEquals('existing-id', $storage->getRequestId());
        $this->assertEquals('existing-id', $request->headers->get('Request-Id'));
    }

    public function testOnResponse(): void
    {
        // 在集成测试中，我们使用真实的容器服务
        $subscriber = self::getService(RequestIdEventSubscriber::class);
        $storage = self::getService(RequestIdStorage::class);

        // 设置一个测试ID
        $storage->setRequestId('test-response-id');

        $request = new Request();
        $response = new Response();
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $subscriber->onResponse($event);

        $this->assertEquals('test-response-id', $response->headers->get('Request-Id'));
    }
}
