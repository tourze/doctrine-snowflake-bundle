<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineSnowflakeBundle\Tests\Integration\Entity\TestEntity;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\SnowflakeBundle\SnowflakeBundle;

/**
 * 测试DoctrineSnowflakeBundle与Doctrine ORM的实际集成
 * 
 * 注意: 运行此测试需要在全局项目中安装以下依赖:
 * - doctrine/doctrine-bundle
 * - symfony/string
 * - symfony/orm-pack (可选)
 */
class DoctrineSnowflakeIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected static function createKernel(array $options = []): IntegrationTestKernel
    {
        $appendBundles = [
            FrameworkBundle::class => ["all" => true],
            DoctrineBundle::class => ["all" => true],
            SnowflakeBundle::class => ["all" => true],
            DoctrineSnowflakeBundle::class => ["all" => true],
        ];
        
        $entityMappings = [
            'Tourze\DoctrineSnowflakeBundle\Tests\Integration\Entity' => __DIR__ . '/Entity',
        ];

        return new IntegrationTestKernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true,
            $appendBundles,
            $entityMappings
        );
    }

    protected function setUp(): void
    {
        // 检查依赖
        $this->checkDependencies();

        // 启动内核
        self::bootKernel();
        $container = static::getContainer();

        // 获取实体管理器
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 数据库模式由通用内核自动创建
    }

    /**
     * 检查测试所需的依赖
     */
    private function checkDependencies(): void
    {
    }

    public function testEntityIdGeneration(): void
    {
        // 获取容器和实体管理器
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试实体
        $entity = new TestEntity();
        $entity->setName('测试实体');

        // 保存实体
        $entityManager->persist($entity);
        $entityManager->flush();

        // 验证是否自动生成了ID
        $id = $entity->getId();
        $this->assertNotEmpty($id);
        $this->assertStringStartsWith('TEST_', $id);
        $this->assertLessThanOrEqual(32, strlen($id));

        // 创建第二个实体并保存
        $entity2 = new TestEntity();
        $entity2->setName('测试实体2');
        $entityManager->persist($entity2);
        $entityManager->flush();

        // 验证第二个ID不等于第一个ID
        $id2 = $entity2->getId();
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id, $id2);
        $this->assertStringStartsWith('TEST_', $id2);
    }

    public function testExistingIdPreservation(): void
    {
        // 获取容器和实体管理器
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试实体并预设ID
        $entity = new TestEntity();
        $entity->setId('TEST_CUSTOM_ID');
        $entity->setName('自定义ID实体');

        // 保存实体
        $entityManager->persist($entity);
        $entityManager->flush();

        // 验证预设ID被保留
        $this->assertEquals('TEST_CUSTOM_ID', $entity->getId());

        // 清除实体管理器
        $entityManager->clear();

        // 从数据库重新加载实体
        $loadedEntity = $entityManager->find(TestEntity::class, 'TEST_CUSTOM_ID');
        $this->assertNotNull($loadedEntity);
        $this->assertEquals('TEST_CUSTOM_ID', $loadedEntity->getId());
        $this->assertEquals('自定义ID实体', $loadedEntity->getName());
    }
}
