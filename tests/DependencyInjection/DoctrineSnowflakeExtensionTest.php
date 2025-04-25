<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineSnowflakeBundle\DependencyInjection\DoctrineSnowflakeExtension;

/**
 * 测试DoctrineSnowflakeExtension依赖注入扩展
 */
class DoctrineSnowflakeExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new DoctrineSnowflakeExtension();

        $extension->load([], $container);

        // 测试服务是否已注册
        $this->assertTrue($container->hasDefinition('Tourze\DoctrineSnowflakeBundle\EventSubscriber\SnowflakeListener'));
        $this->assertTrue($container->hasDefinition('Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator'));
        $this->assertTrue($container->hasDefinition('doctrine-snowflake.property-accessor'));

        // 验证PropertyAccessor服务工厂
        $factory = $container->getDefinition('doctrine-snowflake.property-accessor')->getFactory();
        $this->assertSame('Symfony\Component\PropertyAccess\PropertyAccess', $factory[0]);
        $this->assertSame('createPropertyAccessor', $factory[1]);
    }
}
