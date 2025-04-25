<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineSnowflakeBundle\EventSubscriber\SnowflakeListener;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 测试SnowflakeListener事件订阅者
 */
class SnowflakeListenerTest extends TestCase
{
    private SnowflakeListener $listener;
    private Snowflake|MockObject $snowflake;
    private PropertyAccessor|MockObject $propertyAccessor;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        // 创建模拟对象
        $this->snowflake = $this->createMock(Snowflake::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);

        $this->listener = new SnowflakeListener(
            $this->propertyAccessor,
            $this->snowflake,
            $this->logger
        );
    }

    public function testPrePersist(): void
    {
        // 创建测试实体
        $entity = new TestEntity();

        // 确保没有初始ID值
        $entity->setId('');

        // 模拟Snowflake返回ID
        $this->snowflake
            ->method('id')
            ->willReturn('123456789');

        // 模拟PropertyAccessor行为
        $this->propertyAccessor
            ->method('isWritable')
            ->willReturn(true);

        // 期望PropertyAccessor.setValue被调用，并捕获生成的ID值
        $this->propertyAccessor
            ->expects($this->once())
            ->method('setValue')
            ->with(
                $this->identicalTo($entity),
                $this->equalTo('id'),
                $this->callback(function ($arg) {
                    $this->assertStringStartsWith('TEST_', $arg);
                    return true;
                })
            );

        // 模拟ObjectManager和ClassMetadata
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $metadata = $this->createMock(ClassMetadata::class);

        // 需要创建一个自定义的ReflectionClass，以便能正确识别私有属性
        $reflectionClass = new class(TestEntity::class) extends ReflectionClass {
            public function getProperties($filter = null): array
            {
                if ($filter === ReflectionProperty::IS_PRIVATE) {
                    // 返回我们的测试属性
                    $property = new ReflectionProperty(TestEntity::class, 'id');
                    return [$property];
                }
                return parent::getProperties($filter);
            }
        };

        $metadata->method('getReflectionClass')
            ->willReturn($reflectionClass);

        $objectManager->method('getClassMetadata')
            ->with(TestEntity::class)
            ->willReturn($metadata);

        // 设置logger记录期望
        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringContains('分配雪花算法ID'),
                $this->arrayHasKey('id')
            );

        // 创建PrePersistEventArgs
        $args = new PrePersistEventArgs($entity, $objectManager);

        // 执行测试方法
        $this->listener->prePersist($args);
    }

    public function testPrePersistWithExistingId(): void
    {
        // 创建测试实体并设置已有ID
        $entity = new TestEntity();
        $entity->setId('EXISTING_ID');

        // 模拟PropertyAccessor行为
        $this->propertyAccessor
            ->method('isWritable')
            ->willReturn(true);

        // setValue不应该被调用，因为ID已存在
        $this->propertyAccessor
            ->expects($this->never())
            ->method('setValue');

        // Snowflake不应被调用
        $this->snowflake
            ->expects($this->never())
            ->method('id');

        // 模拟ObjectManager和ClassMetadata
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $metadata = $this->createMock(ClassMetadata::class);

        // 需要创建一个自定义的ReflectionClass，以便能正确识别私有属性
        $reflectionClass = new class(TestEntity::class) extends ReflectionClass {
            public function getProperties($filter = null): array
            {
                if ($filter === ReflectionProperty::IS_PRIVATE) {
                    // 返回我们的测试属性
                    $property = new ReflectionProperty(TestEntity::class, 'id');
                    return [$property];
                }
                return parent::getProperties($filter);
            }
        };

        $metadata->method('getReflectionClass')
            ->willReturn($reflectionClass);

        $objectManager->method('getClassMetadata')
            ->with(TestEntity::class)
            ->willReturn($metadata);

        // 创建PrePersistEventArgs
        $args = new PrePersistEventArgs($entity, $objectManager);

        // 执行测试方法
        $this->listener->prePersist($args);
    }
}

/**
 * 仅用于测试的实体类
 */
class TestEntity
{
    #[SnowflakeColumn(prefix: 'TEST_', length: 32)]
    private string $id = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
