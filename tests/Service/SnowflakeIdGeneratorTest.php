<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;

/**
 * 测试SnowflakeIdGenerator服务
 */
class SnowflakeIdGeneratorTest extends TestCase
{
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

    public function testGenerateId(): void
    {
        // 创建一个模拟实体
        $entity = new class {
            private $id;

            public function getId()
            {
                return $this->id;
            }

            public function setId($id): void
            {
                $this->id = $id;
            }
        };

        // 创建EntityManager模拟对象
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $generator = new SnowflakeIdGenerator();

        // 测试当实体没有ID时，会生成一个新ID
        $id = $generator->generateId($entityManager, $entity);
        $this->assertNotEmpty($id);

        // 测试当实体已有ID时，会返回该ID
        $existingId = '123456789';
        $entity->setId($existingId);
        $this->assertSame($existingId, $generator->generateId($entityManager, $entity));
    }
}
