<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineSnowflakeBundle\EventSubscriber\SnowflakeListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * 测试SnowflakeListener事件订阅者
 *
 * @internal
 */
#[CoversClass(SnowflakeListener::class)]
#[RunTestsInSeparateProcesses]
final class SnowflakeListenerTest extends AbstractEventSubscriberTestCase
{
    private SnowflakeListener $listener;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $listener = $container->get(SnowflakeListener::class);
        $this->assertInstanceOf(SnowflakeListener::class, $listener);
        $this->listener = $listener;
    }

    /**
     * 创建测试实体对象
     *
     * 使用简单匿名类，通过 phpstan-ignore 解决类型安全问题
     * 保持测试的真实性，能够测试实际的业务逻辑
     *
     * @return object 具有测试所需方法的匿名类实例
     */
    private function createTestEntity(): object
    {
        return new class {
            #[SnowflakeColumn(prefix: 'TEST_')]
            private ?string $snowflakeId = null;

            public function getSnowflakeId(): ?string
            {
                return $this->snowflakeId;
            }

            public function setSnowflakeId(?string $snowflakeId): void
            {
                $this->snowflakeId = $snowflakeId;
            }

            public function setName(?string $name): void
            {
                // 简化实现，只保留测试需要的功能
            }
        };
    }

    public function testClassImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf(EntityCheckerInterface::class, $this->listener);
    }

    public function testPrePersist(): void
    {
        // 创建测试实体实例
        $entity = $this->createTestEntity();

        // @phpstan-ignore-next-line method.notFound
        $entity->setName('test_name_' . uniqid());

        // 确保雪花ID初始为空
        // @phpstan-ignore-next-line method.notFound
        $this->assertNull($entity->getSnowflakeId());

        // 创建模拟的ObjectManager，不依赖EntityManager的元数据
        $objectManager = $this->createMock(ObjectManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        // 创建反射类并返回具有SnowflakeColumn属性的属性
        $reflectionClass = new \ReflectionClass($entity);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $objectManager->method('getClassMetadata')->willReturn($metadata);

        // 直接测试prePersistEntity方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证雪花ID已被正确设置
        // @phpstan-ignore-next-line method.notFound
        $this->assertNotNull($entity->getSnowflakeId());
        // @phpstan-ignore-next-line method.notFound
        $this->assertIsString($entity->getSnowflakeId());
        // @phpstan-ignore-next-line method.notFound
        $this->assertNotEmpty($entity->getSnowflakeId());

        // 验证ID是雪花算法生成的，且有前缀
        // @phpstan-ignore-next-line method.notFound
        $this->assertStringStartsWith('TEST_', $entity->getSnowflakeId());

        // 去掉前缀后应该是纯数字
        // @phpstan-ignore-next-line method.notFound
        $snowflakeValue = substr($entity->getSnowflakeId(), 5); // 去掉 'TEST_' 前缀
        $this->assertMatchesRegularExpression('/^\d+$/', $snowflakeValue);
    }

    public function testPrePersistWithExistingId(): void
    {
        // 创建测试实体实例并设置已有雪花ID
        $entity = $this->createTestEntity();

        // @phpstan-ignore-next-line method.notFound
        $entity->setName('test_name_existing_' . uniqid());

        // 手动设置一个已存在的雪花ID
        $existingSnowflakeId = 'TEST_123456789012345678';
        // @phpstan-ignore-next-line method.notFound
        $entity->setSnowflakeId($existingSnowflakeId);

        // 确认雪花ID已设置
        // @phpstan-ignore-next-line method.notFound
        $this->assertSame($existingSnowflakeId, $entity->getSnowflakeId());

        // 创建模拟的ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $reflectionClass = new \ReflectionClass($entity);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $objectManager->method('getClassMetadata')->willReturn($metadata);

        // 直接测试prePersistEntity方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证雪花ID保持不变（不会覆盖已存在的ID）
        // @phpstan-ignore-next-line method.notFound
        $this->assertSame($existingSnowflakeId, $entity->getSnowflakeId());
    }

    public function testPrePersistEntityDirectly(): void
    {
        // 直接测试prePersistEntity方法
        $entity = $this->createTestEntity();

        // @phpstan-ignore-next-line method.notFound
        $this->assertNull($entity->getSnowflakeId());

        // 创建模拟的ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $reflectionClass = new \ReflectionClass($entity);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $objectManager->method('getClassMetadata')->willReturn($metadata);

        // 直接调用prePersistEntity方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证雪花ID已被正确设置
        // @phpstan-ignore-next-line method.notFound
        $this->assertNotNull($entity->getSnowflakeId());
        // @phpstan-ignore-next-line method.notFound
        $this->assertStringStartsWith('TEST_', $entity->getSnowflakeId());
    }

    public function testPreUpdateEntityDirectly(): void
    {
        // 直接测试preUpdateEntity方法（该方法应该什么都不做）
        $entity = $this->createTestEntity();
        $existingSnowflakeId = 'TEST_987654321098765432';

        // @phpstan-ignore-next-line method.notFound
        $entity->setSnowflakeId($existingSnowflakeId);

        // 创建模拟的ObjectManager和PreUpdateEventArgs
        $objectManager = $this->createMock(ObjectManager::class);
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);

        // 直接调用preUpdateEntity方法
        $this->listener->preUpdateEntity($objectManager, $entity, $eventArgs);

        // 验证雪花ID保持不变（更新时不处理ID）
        // @phpstan-ignore-next-line method.notFound
        $this->assertSame($existingSnowflakeId, $entity->getSnowflakeId());
    }

    public function testListenerOnlyHandlesPrePersist(): void
    {
        // 验证监听器只处理prePersist事件
        $entity = $this->createTestEntity();

        // @phpstan-ignore-next-line method.notFound
        $entity->setName('test_name_update_' . uniqid());

        // 设置一个已存在的雪花ID
        $existingSnowflakeId = 'TEST_987654321098765432';
        // @phpstan-ignore-next-line method.notFound
        $entity->setSnowflakeId($existingSnowflakeId);

        // 确认雪花ID已设置
        // @phpstan-ignore-next-line method.notFound
        $this->assertSame($existingSnowflakeId, $entity->getSnowflakeId());

        // 创建模拟的ObjectManager和PreUpdateEventArgs
        $objectManager = $this->createMock(ObjectManager::class);
        $changeSet = [];
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);

        // 直接调用preUpdateEntity方法（该方法什么都不做）
        $this->listener->preUpdateEntity($objectManager, $entity, $eventArgs);

        // 验证雪花ID保持不变（更新时不处理ID）
        // @phpstan-ignore-next-line method.notFound
        $this->assertSame($existingSnowflakeId, $entity->getSnowflakeId());
    }
}
