<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RequestIdBundle\Middleware\RequestIdMiddleware;
use RequestIdBundle\Service\RequestIdStorage;
use RequestIdBundle\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

/**
 * @internal
 */
#[CoversClass(RequestIdMiddleware::class)]
final class RequestIdMiddlewareTest extends TestCase
{
    private RequestIdStorage $storage;

    private RequestIdMiddleware $middleware;

    private StackMiddleware $stack;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new RequestIdStorage();
        $this->middleware = new RequestIdMiddleware($this->storage);

        // 创建匿名类替换 StackMiddleware Mock
        $this->stack = new class extends StackMiddleware {
            public function __construct()
            {
                // 调用父类构造函数，传递空栈以避免初始化真实的中间件栈
                parent::__construct($this);
            }

            public function next(): StackMiddleware
            {
                return $this;
            }

            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                return $envelope;
            }
        };
    }

    public function testHandleProducerMessage(): void
    {
        // 准备测试数据 - 设置请求 ID
        $this->storage->setRequestId('test-request-id');

        // 创建一个没有 RequestIdStamp 的信封
        $message = new \stdClass();
        $envelope = new Envelope($message);

        // 调用中间件
        $resultEnvelope = $this->middleware->handle($envelope, $this->stack);

        // 验证结果信封中包含 RequestIdStamp，并且 ID 正确
        $stamp = $resultEnvelope->last(RequestIdStamp::class);
        $this->assertInstanceOf(RequestIdStamp::class, $stamp);
        $this->assertEquals('test-request-id', $stamp->getRequestId());
    }

    public function testHandleProducerMessageWithoutStorageId(): void
    {
        // 没有设置请求 ID
        $this->storage->setRequestId(null);

        // 创建一个没有 RequestIdStamp 的信封
        $message = new \stdClass();
        $envelope = new Envelope($message);

        // 调用中间件
        $resultEnvelope = $this->middleware->handle($envelope, $this->stack);

        // 验证结果信封中没有添加 RequestIdStamp
        $stamp = $resultEnvelope->last(RequestIdStamp::class);
        $this->assertNull($stamp);
    }

    public function testHandleConsumerMessage(): void
    {
        // 创建带有 RequestIdStamp 和 ConsumedByWorkerStamp 的信封，模拟消费场景
        $message = new \stdClass();
        $envelope = new Envelope($message);
        $envelope = $envelope->with(new RequestIdStamp('consumer-request-id'));
        $envelope = $envelope->with(new ConsumedByWorkerStamp());

        // 调用中间件
        $this->middleware->handle($envelope, $this->stack);

        // 验证 RequestIdStorage 中的值被设置为信封中的 ID
        $this->assertEquals('consumer-request-id', $this->storage->getRequestId());
    }
}
