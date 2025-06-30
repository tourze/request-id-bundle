<?php

namespace RequestIdBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RequestIdBundle\DependencyInjection\RequestIdExtension;
use RequestIdBundle\EventSubscriber\CommandRequestIdSubscriber;
use RequestIdBundle\EventSubscriber\RequestIdSubscriber;
use RequestIdBundle\Middleware\RequestIdMiddleware;
use RequestIdBundle\Processor\MessengerProcessor;
use RequestIdBundle\Processor\RequestIdProcessor;
use RequestIdBundle\Service\RequestIdGenerator;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RequestIdExtensionTest extends TestCase
{
    public function testLoadServicesConfiguration(): void
    {
        $container = new ContainerBuilder();
        $extension = new RequestIdExtension();
        
        $extension->load([], $container);
        
        // 验证服务类是否被自动注册
        $this->assertTrue($container->hasDefinition(RequestIdGenerator::class));
        $this->assertTrue($container->hasDefinition(RequestIdStorage::class));
        $this->assertTrue($container->hasDefinition(RequestIdSubscriber::class));
        $this->assertTrue($container->hasDefinition(CommandRequestIdSubscriber::class));
        $this->assertTrue($container->hasDefinition(RequestIdMiddleware::class));
        $this->assertTrue($container->hasDefinition(MessengerProcessor::class));
        $this->assertTrue($container->hasDefinition(RequestIdProcessor::class));
    }
}