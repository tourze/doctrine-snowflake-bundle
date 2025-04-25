<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineEntityCheckerBundle\DoctrineEntityCheckerBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;

/**
 * 测试DoctrineSnowflakeBundle类
 */
class DoctrineSnowflakeBundleTest extends TestCase
{
    public function testGetBundleDependencies(): void
    {
        $dependencies = DoctrineSnowflakeBundle::getBundleDependencies();

        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(DoctrineEntityCheckerBundle::class, $dependencies);
        $this->assertIsArray($dependencies[DoctrineEntityCheckerBundle::class]);
        $this->assertArrayHasKey('all', $dependencies[DoctrineEntityCheckerBundle::class]);
        $this->assertTrue($dependencies[DoctrineEntityCheckerBundle::class]['all']);
    }
}
