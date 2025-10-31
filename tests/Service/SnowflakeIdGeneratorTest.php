<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineSnowflakeBundle\Exception\EntityWithoutIdMethodException;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * 测试SnowflakeIdGenerator服务的静态方法
 *
 * @internal
 */
#[CoversClass(SnowflakeIdGenerator::class)]
#[RunTestsInSeparateProcesses]
final class SnowflakeIdGeneratorTest extends AbstractIntegrationTestCase
{
    private SnowflakeIdGenerator $generator;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $generator = $container->get(SnowflakeIdGenerator::class);
        $this->assertInstanceOf(SnowflakeIdGenerator::class, $generator);
        $this->generator = $generator;
    }

    /**
     * 创建测试实体对象，用于测试generateId方法
     */
    private function createTestEntityWithId(?string $id = null): object
    {
        return new class($id) {
            private ?string $id;

            public function __construct(?string $id = null)
            {
                $this->id = $id;
            }

            public function getId(): ?string
            {
                return $this->id;
            }

            public function setId(?string $id): void
            {
                $this->id = $id;
            }
        };
    }

    /**
     * 创建没有getId方法的测试实体
     */
    private function createTestEntityWithoutId(): object
    {
        return new class {
            private string $name = 'test';

            public function getName(): string
            {
                return $this->name;
            }
        };
    }

    public function testGenerateIdWithNullEntity(): void
    {
        // 测试当entity为null时的行为
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $result = $this->generator->generateId($entityManager, null);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^\d+$/', $result);
    }

    public function testGenerateIdWithEntityWithoutIdMethod(): void
    {
        // 测试实体没有getId方法时应该抛出异常
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entity = $this->createTestEntityWithoutId();

        $this->expectException(EntityWithoutIdMethodException::class);
        $this->generator->generateId($entityManager, $entity);
    }

    public function testGenerateIdWithEntityWithExistingId(): void
    {
        // 测试实体已有ID时应该返回现有ID
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $existingId = 'existing_12345';
        $entity = $this->createTestEntityWithId($existingId);

        $result = $this->generator->generateId($entityManager, $entity);

        $this->assertSame($existingId, $result);
    }

    public function testGenerateIdWithEntityWithEmptyId(): void
    {
        // 测试实体ID为空字符串时应该生成新ID
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entity = $this->createTestEntityWithId(''); // 空字符串ID

        $result = $this->generator->generateId($entityManager, $entity);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^\d+$/', $result);
    }

    public function testGenerateIdWithEntityWithNullId(): void
    {
        // 测试实体ID为null时应该生成新ID
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entity = $this->createTestEntityWithId(); // 默认为null

        $this->assertNull($entity->getId());

        $result = $this->generator->generateId($entityManager, $entity);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^\d+$/', $result);
    }

    public function testGenerateDataCenterIdFromClassName(): void
    {
        $id1 = SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\User');
        $id2 = SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\Order');

        // 确保同一个类名总是生成相同的ID
        $this->assertSame($id1, SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\User'));
        $this->assertSame($id2, SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\Order'));

        // 确保不同类名生成不同的ID
        $this->assertNotSame($id1, $id2);

        // 测试ID在有效范围内
        $this->assertGreaterThanOrEqual(0, $id1);
        $this->assertLessThanOrEqual(31, $id1);
        $this->assertGreaterThanOrEqual(0, $id2);
        $this->assertLessThanOrEqual(31, $id2);
    }

    public function testDataCenterIdGeneration(): void
    {
        // 测试同一类名总是生成相同的ID
        $className = 'App\Entity\User';
        $id1 = SnowflakeIdGenerator::generateDataCenterIdFromClassName($className);
        $id2 = SnowflakeIdGenerator::generateDataCenterIdFromClassName($className);
        $this->assertSame($id1, $id2, '同一类名应该生成相同的数据中心ID');

        // 测试不同类名生成不同的ID
        $userClassId = SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\User');
        $orderClassId = SnowflakeIdGenerator::generateDataCenterIdFromClassName('App\Entity\Order');
        $this->assertNotSame($userClassId, $orderClassId, '不同类名应该生成不同的数据中心ID');

        // 测试边界值 - 默认最大值31
        $this->assertGreaterThanOrEqual(0, $userClassId);
        $this->assertLessThanOrEqual(31, $userClassId);
        $this->assertGreaterThanOrEqual(0, $orderClassId);
        $this->assertLessThanOrEqual(31, $orderClassId);

        // 测试自定义最大值
        $id = SnowflakeIdGenerator::generateDataCenterIdFromClassName('TestClass', 15);
        $this->assertGreaterThanOrEqual(0, $id);
        $this->assertLessThanOrEqual(15, $id);
    }
}
