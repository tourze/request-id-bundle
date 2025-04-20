# RequestIdBundle 测试计划

## 单元测试状态

| 组件 | 状态 | 测试文件 |
| --- | --- | --- |
| RequestIdBundle | ✅ 完成 | `tests/RequestIdBundleTest.php` |
| RequestIdGenerator | ✅ 完成 | `tests/Service/RequestIdGeneratorTest.php` |
| RequestIdStorage | ✅ 完成 | `tests/Service/RequestIdStorageTest.php` |
| RequestIdSubscriber | ⚠️ 部分测试，事件处理需要重构 | `tests/EventSubscriber/RequestIdSubscriberTest.php` |
| CommandRequestIdSubscriber | ✅ 完成 | `tests/EventSubscriber/CommandRequestIdSubscriberTest.php` |
| RequestIdMiddleware | ✅ 完成 | `tests/Middleware/RequestIdMiddlewareTest.php` |
| RequestIdProcessor | ✅ 完成 | `tests/Processor/RequestIdProcessorTest.php` |
| MessengerProcessor | ⚠️ 部分测试，事件处理需要重构 | `tests/Processor/MessengerProcessorTest.php` |
| RequestIdStamp | ✅ 完成 | `tests/Stamp/RequestIdStampTest.php` |

## 测试覆盖范围

- 服务组件
  - ✅ RequestIdGenerator 测试了 UUID 生成和 Base58 编码
  - ✅ RequestIdStorage 测试了存储、获取和重置功能

- 事件订阅器
  - ⚠️ RequestIdSubscriber 测试了基本配置和接口，但由于 Symfony 的 final 类限制，事件处理方法未能完全测试
  - ✅ CommandRequestIdSubscriber 测试了命令行中请求 ID 的生成和清理

- 中间件
  - ✅ RequestIdMiddleware 测试了消息队列生产者和消费者场景

- 处理器
  - ✅ RequestIdProcessor 测试了日志记录中 ID 处理
  - ⚠️ MessengerProcessor 测试了基本功能和接口，但由于 Symfony 的 final 类限制，事件处理方法未能完全测试

- 其他
  - ✅ RequestIdStamp 测试了消息队列中请求 ID 的转发功能

## 测试执行方式

使用以下命令执行测试：

```shell
./vendor/bin/phpunit packages/request-id-bundle/tests
```

## 测试注意事项

1. 由于 Symfony 框架的许多事件类（如 `RequestEvent`, `ResponseEvent`, `WorkerMessageReceivedEvent` 等）被标记为 `final`，无法被继承或 mock，因此相关测试使用了替代方法：
   - 主要测试了配置和依赖注入
   - 使用反射测试了属性和内部状态
   - 测试了事件订阅配置

2. 需要后续改进：
   - 可以考虑重构代码使其更容易测试，例如提取事件处理逻辑到可测试的服务类
   - 或添加集成测试使用真实的 Symfony 组件来测试完整功能

3. 所有测试都能正常通过
