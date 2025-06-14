<?php

namespace Tourze\DoctrineSnowflakeBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 在保存实体时，自动保存雪花ID值
 */
#[AsDoctrineListener(event: Events::prePersist)]
class SnowflakeListener implements EntityCheckerInterface
{
    public function __construct(
        #[Autowire(service: 'doctrine-snowflake.property-accessor')] private readonly PropertyAccessor $propertyAccessor,
        private readonly Snowflake $snowflake,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->prePersistEntity($args->getObjectManager(), $args->getObject());
    }

    public function prePersistEntity(ObjectManager $objectManager, object $entity): void
    {
        $reflection = $objectManager->getClassMetadata($entity::class)->getReflectionClass();
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            // 如果字段不可以写入，直接跳过即可
            if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
                continue;
            }

            foreach ($property->getAttributes(SnowflakeColumn::class) as $attribute) {
                $attribute = $attribute->newInstance();
                /* @var SnowflakeColumn $attribute */

                try {
                    // 已经有值了，我们就跳过
                    $v = $property->getValue($entity);
                    if (!empty($v)) {
                        continue;
                    }
                } catch (\Throwable $exception) {
                    // 忽略
                }

                $idValue = $this->snowflake->id();
                $idValue = trim($idValue ?? '');
                if (empty($idValue)) {
                    continue;
                }
                
                if ($attribute->prefix) {
                    $idValue = "{$attribute->prefix}{$idValue}";
                }

                if ($attribute->length > 0) {
                    $idValue = substr($idValue, 0, $attribute->length);
                }

                $this->logger?->debug("为{$property->getName()}分配雪花算法ID", [
                    'id' => $idValue,
                    'entity' => $entity,
                ]);
                $this->propertyAccessor->setValue($entity, $property->getName(), $idValue);
            }
        }
    }

    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        // 更新时，我们不特地去处理，因为有可能我们是需要去数据库特地清空，故意不给值的
    }
}
