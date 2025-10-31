<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineSnowflakeBundle\DependencyInjection\DoctrineSnowflakeExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * 测试DoctrineSnowflakeExtension依赖注入扩展
 *
 * @internal
 */
#[CoversClass(DoctrineSnowflakeExtension::class)]
final class DoctrineSnowflakeExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testGetConfigDir(): void
    {
        $extension = new DoctrineSnowflakeExtension();
        $reflection = new \ReflectionClass($extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($extension);
        $this->assertIsString($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertDirectoryExists($configDir);
    }
}
