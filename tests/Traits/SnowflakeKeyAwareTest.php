<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Traits;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineSnowflakeBundle\Tests\Traits\Entity\SnowflakeKeyAwareTestEntity;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\SnowflakeBundle\SnowflakeBundle;

/**
 * 测试 SnowflakeKeyAware trait
 */
class SnowflakeKeyAwareTest extends KernelTestCase
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
            'Tourze\DoctrineSnowflakeBundle\Tests\Traits\Entity' => __DIR__ . '/Entity',
        ];

        return new IntegrationTestKernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true,
            $appendBundles,
            $entityMappings
        );
    }

    /**
     * 测试初始状态
     */
    public function testInitialState(): void
    {
        $entity = new SnowflakeKeyAwareTestEntity();

        // 验证初始ID为'0'
        $this->assertEquals('0', $entity->getId());
    }

    /**
     * 测试手动设置ID
     */
    public function testSetId(): void
    {
        $entity = new SnowflakeKeyAwareTestEntity();

        // 设置ID
        $entity->setId('123456789');
        $this->assertEquals('123456789', $entity->getId());

        // 设置null
        $entity->setId(null);
        $this->assertNull($entity->getId());
    }

    /**
     * 测试自动生成雪花ID
     */
    public function testAutoGenerateSnowflakeId(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建新实体
        $entity = new SnowflakeKeyAwareTestEntity();
        $entity->setName('测试实体');

        // 保存实体
        $entityManager->persist($entity);
        $entityManager->flush();

        // 验证ID被自动生成
        $id = $entity->getId();
        $this->assertNotNull($id);
        $this->assertNotEquals('0', $id);

        // 雪花ID应该是一个大数字的字符串表示
        $this->assertMatchesRegularExpression('/^\d+$/', $id);
        $this->assertGreaterThan(1000000000000, (int)$id);
    }

    /**
     * 测试保留预设ID
     */
    public function testPreservePresetId(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建实体并预设ID
        $entity = new SnowflakeKeyAwareTestEntity();
        $entity->setId('999999999999999');
        $entity->setName('预设ID实体');

        // 保存实体
        $entityManager->persist($entity);
        $entityManager->flush();

        // 验证ID被保留
        $this->assertEquals('999999999999999', $entity->getId());

        // 清除实体管理器
        $entityManager->clear();

        // 从数据库重新加载
        $loadedEntity = $entityManager->find(SnowflakeKeyAwareTestEntity::class, '999999999999999');
        $this->assertNotNull($loadedEntity);
        $this->assertEquals('999999999999999', $loadedEntity->getId());
        $this->assertEquals('预设ID实体', $loadedEntity->getName());
    }

    /**
     * 测试多个实体生成不同的ID
     */
    public function testUniqueIdGeneration(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $ids = [];

        // 创建多个实体
        for ($i = 0; $i < 5; $i++) {
            $entity = new SnowflakeKeyAwareTestEntity();
            $entity->setName('实体' . $i);
            $entityManager->persist($entity);
            $entityManager->flush();

            $ids[] = $entity->getId();
        }

        // 验证所有ID都不相同
        $uniqueIds = array_unique($ids);
        $this->assertCount(5, $uniqueIds);

        // 验证所有ID都是有效的雪花ID
        foreach ($ids as $id) {
            $this->assertNotNull($id);
            $this->assertNotEquals('0', $id);
            $this->assertMatchesRegularExpression('/^\d+$/', $id);
        }
    }

    /**
     * 测试ID作为字符串处理（防止JS精度问题）
     */
    public function testIdAsString(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $entity = new SnowflakeKeyAwareTestEntity();
        $entity->setName('字符串ID测试');

        $entityManager->persist($entity);
        $entityManager->flush();

        // 确保ID是字符串类型
        $id = $entity->getId();
        // 即使进行数学运算，也应该保持字符串精度
        $this->assertEquals($id, strval($id));
    }

    /**
     * 测试使用自定义ID生成器
     */
    public function testCustomIdGeneratorConfiguration(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 获取实体元数据
        $metadata = $entityManager->getClassMetadata(SnowflakeKeyAwareTestEntity::class);

        // 验证ID字段配置
        $idField = $metadata->getFieldMapping('id');
        $this->assertEquals('bigint', $idField->type);
        $this->assertFalse($idField->nullable);
        $this->assertEquals('ID', $idField->options['comment']);

        // 验证自定义ID生成器配置
        $this->assertTrue($metadata->usesIdGenerator());
        $this->assertEquals(7, $metadata->generatorType); // ClassMetadata::GENERATOR_TYPE_CUSTOM = 7
        $this->assertEquals('Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator', $metadata->customGeneratorDefinition['class']);
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }
}