<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineSnowflakeBundle\Tests\Traits\TestEntityWithSnowflakeKey;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;

/**
 * 测试 SnowflakeKeyAware trait
 *
 * @internal
 */
#[CoversClass(SnowflakeKeyAware::class)]
final class SnowflakeKeyAwareTest extends TestCase
{
    /**
     * 创建测试实体对象
     *
     * 使用专门的测试实体类来避免 PHPStan 对匿名类的限制
     * 保持测试的真实性，能够测试实际的 Trait 功能
     *
     * @return TestEntityWithSnowflakeKey 具有测试所需方法的测试实体实例
     */
    private function createTestEntity(): TestEntityWithSnowflakeKey
    {
        return new TestEntityWithSnowflakeKey();
    }

    /**
     * 测试初始状态
     */
    public function testInitialState(): void
    {
        $entity = $this->createTestEntity();

        // 验证初始ID为null（根据实体设计）
        $this->assertNull($entity->getId());
    }

    /**
     * 测试手动设置ID
     */
    public function testSetId(): void
    {
        $entity = $this->createTestEntity();

        // 设置ID
        // @phpstan-ignore-next-line method.notFound
        $entity->setId('123456789');
        $this->assertEquals('123456789', $entity->getId());

        // 设置 null
        // @phpstan-ignore-next-line method.notFound
        $entity->setId(null);
        $this->assertNull($entity->getId());
    }

    /**
     * 测试 trait 的基本功能
     */
    public function testTraitBasicFunctionality(): void
    {
        $entity = $this->createTestEntity();

        // 测试默认值
        $this->assertNull($entity->getId());

        // 测试设置和获取
        $testId = 'test_snowflake_id_123';
        // @phpstan-ignore-next-line method.notFound
        $entity->setId($testId);
        $this->assertEquals($testId, $entity->getId());

        // 测试设置不同的值
        // @phpstan-ignore-next-line method.notFound
        $entity->setId('another_id');
        $this->assertEquals('another_id', $entity->getId());
    }
}
