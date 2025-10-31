# RequestIdBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)
[![License](https://img.shields.io/packagist/l/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)
[![Build Status](https://img.shields.io/travis/tourze/request-id-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/request-id-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/request-id-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/request-id-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/request-id-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/request-id-bundle/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)

一个基于 Symfony 的请求 ID 管理包，用于在整个应用程序中跟踪和关联请求。支持 HTTP 请求、消息队列和命令行命令，为分布式系统提供全面的请求追踪能力。

## 功能特性

- 基于 UUID 生成唯一请求 ID，使用 Base58 编码（比标准 UUID 格式更短）
- 协程安全的请求 ID 存储，具有自动重置机制，防止内存泄漏
- 自动在 HTTP 请求/响应头添加请求 ID
- 通过 Symfony Messenger 中间件在消息队列中传递请求 ID
- 为命令行命令生成带有特殊 "CLI" 前缀的请求 ID
- 通过 Monolog 处理器自动将请求 ID 集成到日志中
- 支持跨不同服务的分布式系统请求追踪

## 安装说明

```bash
composer require tourze/request-id-bundle
```

**环境要求：**

- PHP >= 8.1
- Symfony >= 6.4
- Symfony 组件：HTTP Kernel、Messenger、Console、Uid
- Monolog >= 3.1
- 完整依赖请查看 composer.json

## 快速开始

### 1. HTTP 请求集成

该包会自动：

- 检查传入 HTTP 头中是否存在请求 ID
- 如果不存在或不被信任，则生成新的 ID
- 在响应头中设置 ID
- 将 ID 存储在协程安全的存储中

```php
// 在控制器中，你可以访问请求 ID：
use RequestIdBundle\Service\RequestIdStorage;

class MyController
{
    public function index(RequestIdStorage $requestIdStorage)
    {
        $currentRequestId = $requestIdStorage->getRequestId();
        // 使用请求 ID...
    }
}
```

### 2. 消息队列集成

请求 ID 会自动通过消息队列传播：

```php
// 消息会自动携带当前的请求 ID
$messageBus->dispatch(new MyMessage());

// 在消息处理器中，可以获取原始请求 ID：
public function handleMessage(MyMessage $message, RequestIdStorage $requestIdStorage)
{
    $originalRequestId = $requestIdStorage->getRequestId();
    // 使用原始请求上下文进行处理...
}
```

### 3. 命令行支持

命令行命令会自动获得带有 "CLI" 前缀的请求 ID：

```bash
$ php bin/console my:command
# 会自动生成类似 "CLIxxxxx" 的请求 ID
```

```php
// 在你的命令中：
use RequestIdBundle\Service\RequestIdStorage;

class MyCommand extends Command
{
    private RequestIdStorage $requestIdStorage;

    public function __construct(RequestIdStorage $requestIdStorage)
    {
        $this->requestIdStorage = $requestIdStorage;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandRequestId = $this->requestIdStorage->getRequestId(); // CLI[uuid]
        // 使用请求 ID...
    }
}
```

### 4. 日志集成

请求 ID 会自动添加到日志记录中：

```php
$logger->info('处理用户请求', ['user_id' => 123]);
// 日志输出将包含 "request_id": "7fT9P5RoJ3a..."
```

## 工作原理

详细的工作流程图，请参阅 [WORKFLOW.md](WORKFLOW.md)。

1. **HTTP 请求：**
    - `RequestIdSubscriber` 处理请求/响应事件
    - 检查请求头中是否存在请求 ID
    - 如果被信任则使用现有 ID，否则生成新 ID
    - 在响应头中添加 ID

2. **消息队列：**
    - `RequestIdMiddleware` 拦截消息的分发/处理
    - 为发出的消息附加 `RequestIdStamp`
    - 在消费消息时恢复原始请求 ID
    - 处理完消息后进行清理

3. **命令行命令：**
    - `CommandRequestIdSubscriber` 生成带有 "CLI" 前缀的请求 ID
    - 在命令开始时设置 ID
    - 在命令终止时进行清理

4. **日志集成：**
    - `RequestIdProcessor` 为所有日志记录添加请求 ID
    - 使请求 ID 在日志格式化程序和输出中可用

## 性能优化

- 使用 Base58 编码，比标准 UUID 生成更短的 ID
- 协程支持确保在异步环境中的线程安全
- 自动清理防止长时间运行的进程中出现内存泄漏
- 中间件方法避免对非请求路径产生性能影响

## 重要说明

- HTTP 请求 ID：UUID Base58 编码
- CLI 请求 ID："CLI" + UUID Base58 编码
- 存储机制基于协程，确保请求隔离
- 自动集成到 Symfony 的日志系统
- 支持分布式系统中的请求跟踪

## 调试建议

- 检查请求/响应头中的请求 ID
- 在日志中搜索特定请求 ID
- 使用请求 ID 关联消息队列任务

## 扩展开发

- 可自定义请求 ID 生成器
- 可扩展请求 ID 传播机制
- 可扩展日志处理器功能

## 贡献指南

欢迎贡献！请查看我们的[贡献指南](https://github.com/tourze/request-id-bundle/blob/master/.github/CONTRIBUTING.md)了解详情。

## 许可证

MIT 许可证。详情请查看 [许可证文件](LICENSE)。
