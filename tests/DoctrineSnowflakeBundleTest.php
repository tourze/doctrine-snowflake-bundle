<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\SnowflakeBundle\SnowflakeBundle;

/**
 * 测试DoctrineSnowflakeBundle类
 */
class DoctrineSnowflakeBundleTest extends TestCase
{
    public function testBundle(): void
    {
        $bundle = new DoctrineSnowflakeBundle();
        
        // 验证Bundle实例创建成功
        $this->assertInstanceOf(DoctrineSnowflakeBundle::class, $bundle);
        
        // 验证Bundle名称格式
        $this->assertEquals('DoctrineSnowflakeBundle', $bundle->getName());
        
        // 验证Bundle路径
        $this->assertStringContainsString('doctrine-snowflake-bundle', $bundle->getPath());
    }
    
    public function testBundleDependency(): void
    {
        // 在实际应用中，该Bundle依赖于SnowflakeBundle
        // 此测试确保依赖的Bundle可以正确实例化
        $dependencyBundle = new SnowflakeBundle();
        $this->assertInstanceOf(SnowflakeBundle::class, $dependencyBundle);
    }
}
