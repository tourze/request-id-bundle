# RequestIdBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)
[![Build Status](https://img.shields.io/travis/tourze/request-id-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/request-id-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/request-id-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/request-id-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/request-id-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/request-id-bundle)

A Symfony bundle for request ID management, enabling tracking and correlation of requests across your application. Supports HTTP, message queue, and CLI commands for comprehensive request tracing in distributed systems.

## Features

- Generate unique request IDs using UUID with Base58 encoding (shorter than standard UUID format)
- Coroutine-safe request ID storage with automatic reset mechanism to prevent memory leaks
- Automatically add request ID to HTTP request/response headers
- Propagate request ID in message queues via Symfony Messenger middleware
- Generate request ID for CLI commands with special "CLI" prefix
- Integrate request ID into logs automatically via Monolog processor
- Support distributed system tracing across different services

## Installation

```bash
composer require tourze/request-id-bundle
```

**Requirements:**

- PHP >= 8.1
- Symfony >= 6.4
- Symfony components: HTTP Kernel, Messenger, Console, Uid
- Monolog >= 3.1
- See composer.json for complete dependencies

## Quick Start

### 1. HTTP Integration

The bundle automatically:

- Checks for request ID in incoming HTTP headers
- Generates new ID if none exists or not trusted
- Sets ID in response headers
- Stores ID in coroutine-safe storage

```php
// In your controllers, you can access the request ID:
use RequestIdBundle\Service\RequestIdStorage;

class MyController
{
    public function index(RequestIdStorage $requestIdStorage)
    {
        $currentRequestId = $requestIdStorage->getRequestId();
        // Use the request ID...
    }
}
```

### 2. Message Queue Integration

Request IDs are automatically propagated through message queues:

```php
// The message will automatically carry the current request ID
$messageBus->dispatch(new MyMessage());

// In message handlers, the original request ID is available:
public function handleMessage(MyMessage $message, RequestIdStorage $requestIdStorage)
{
    $originalRequestId = $requestIdStorage->getRequestId();
    // Process with original request context...
}
```

### 3. CLI Support

CLI commands automatically get request IDs with a "CLI" prefix:

```bash
$ php bin/console my:command
# A request ID like "CLIxxxxx" will be generated automatically
```

```php
// In your commands:
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
        // Use request ID...
    }
}
```

### 4. Log Integration

Request IDs are automatically added to log records:

```php
$logger->info('Processing user request', ['user_id' => 123]);
// Log output will include "request_id": "7fT9P5RoJ3a..."
```

## How It Works

For a detailed workflow diagram, see [WORKFLOW.md](WORKFLOW.md).

1. **HTTP Requests:**
   - `RequestIdSubscriber` handles request/response events
   - Checks if request ID exists in headers
   - Uses existing ID if trusted, otherwise generates new one
   - Adds ID to response headers

2. **Message Queue:**
   - `RequestIdMiddleware` intercepts message dispatching/handling
   - Attaches `RequestIdStamp` to outgoing messages
   - Restores original request ID when consuming messages
   - Cleans up after message handling

3. **CLI Commands:**
   - `CommandRequestIdSubscriber` generates "CLI"-prefixed request ID
   - Sets ID at command start
   - Cleans up at command termination

4. **Log Integration:**
   - `RequestIdProcessor` adds request ID to all log records
   - Makes request ID available in log formatters and outputs

## Performance Optimization

- Uses Base58 encoding for shorter IDs than standard UUIDs
- Coroutine support ensures thread safety in async environments
- Automatic cleanup prevents memory leaks in long-running processes
- Middleware approach avoids performance impact on non-request paths

## Contributing

Contributions are welcome! Please see our [contribution guidelines](https://github.com/tourze/request-id-bundle/blob/master/.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
