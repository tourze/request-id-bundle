# RequestIdBundle

一个基于 Symfony 的请求 ID 管理包,用于在整个应用程序中跟踪和关联请求。支持 HTTP 请求、消息队列和命令行命令。

## 依赖

- AopCoroutineBundle

## 核心功能

### 1. 请求 ID 生成和存储

- 基于 UUID 的请求 ID 生成(使用 Base58 编码以获得更短的 ID)
- 协程安全的请求 ID 存储
- 自动重置机制

### 2. HTTP 请求集成

自动在 HTTP 请求和响应中添加请求 ID:

```yaml
# config/packages/request_id.yaml
request_id:
    request_header: 'Request-Id'  # 默认
    response_header: 'Request-Id' # 默认
    trust_request: true          # 是否信任请求中的 ID
```

### 3. 消息队列集成

自动在消息队列中传递请求 ID:

```php
// 消息会自动携带当前请求的 ID
$messageBus->dispatch(new MyMessage());

// 在消费者中可以访问原始请求的 ID
$requestId = $requestIdStorage->getRequestId();
```

### 4. 命令行支持

为命令行命令生成唯一的请求 ID:

```bash
# 执行命令时会自动生成类似 "CLIxxxxx" 的请求 ID
$ php bin/console my:command
```

### 5. 日志集成

自动在日志记录中包含请求 ID:

```php
// 日志中会自动包含请求 ID
$logger->info('Something happened');
```

## 工作原理

1. HTTP 请求:
   - 检查请求头中是否存在请求 ID
   - 如果存在且配置为信任,则使用该 ID
   - 否则生成新的请求 ID
   - 在响应头中返回请求 ID

2. 消息队列:
   - 发送消息时自动添加 RequestIdStamp
   - 消费消息时恢复原始请求 ID
   - 消费完成后自动清理

3. 命令行:
   - 命令开始时生成带 "CLI" 前缀的请求 ID
   - 命令结束时自动清理

## 性能优化

1. 使用 Base58 编码生成更短的 ID
2. 协程支持,确保线程安全
3. 自动清理机制,防止内存泄漏

## 重要说明

1. 请求 ID 格式:
   - HTTP: UUID Base58 编码
   - CLI: "CLI" + UUID Base58 编码
2. 存储机制基于协程,确保请求隔离
3. 自动集成到 Symfony 的日志系统
4. 支持分布式系统中的请求跟踪

## 调试建议

1. 检查请求/响应头中的请求 ID
2. 在日志中搜索特定请求 ID
3. 使用请求 ID 关联消息队列任务

## 扩展开发

1. 自定义请求 ID 生成器
2. 添加额外的请求 ID 传播机制
3. 扩展日志处理器功能
