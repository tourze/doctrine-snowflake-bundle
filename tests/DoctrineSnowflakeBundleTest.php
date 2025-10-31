<?php

declare(strict_types=1);

namespace Tourze\DoctrineSnowflakeBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineSnowflakeBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineSnowflakeBundleTest extends AbstractBundleTestCase
{
}
