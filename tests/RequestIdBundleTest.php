<?php

declare(strict_types=1);

namespace RequestIdBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RequestIdBundle\RequestIdBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(RequestIdBundle::class)]
#[RunTestsInSeparateProcesses]
final class RequestIdBundleTest extends AbstractBundleTestCase
{
}
