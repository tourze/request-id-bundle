<?php

namespace RequestIdBundle\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\Middleware\RequestIdMiddleware;
use RequestIdBundle\Service\RequestIdStorage;
use RequestIdBundle\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

class RequestIdMiddlewareTest extends TestCase
{
    private RequestIdStorage $storage;
    private RequestIdMiddleware $middleware;
    /** @var StackMiddleware&\PHPUnit\Framework\MockObject\MockObject */
    private $stack;

    protected function setUp(): void
    {
        $this->storage = new RequestIdStorage();
        $this->middleware = new RequestIdMiddleware($this->storage);
        
        // 创建消息队列 stack 的 mock
        /** @var StackMiddleware&\PHPUnit\Framework\MockObject\MockObject $stack */
        $stack = $this->createMock(StackMiddleware::class);
        $self = $this;
        $stack->expects($this->any())
            ->method('next')
            ->willReturn($stack);
        $stack->expects($this->any())
            ->method('handle')
            ->willReturnCallback(function (Envelope $envelope) use ($self) {
                return $envelope;
            });
        
        $this->stack = $stack;
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
        $envelope = (new Envelope($message))
            ->with(new RequestIdStamp('consumer-request-id'))
            ->with(new ConsumedByWorkerStamp());
        
        // 调用中间件
        $this->middleware->handle($envelope, $this->stack);
        
        // 验证 RequestIdStorage 中的值被设置为信封中的 ID
        $this->assertEquals('consumer-request-id', $this->storage->getRequestId());
    }
}
