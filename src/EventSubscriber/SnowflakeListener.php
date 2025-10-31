<?php

declare(strict_types=1);

namespace Tourze\DoctrineSnowflakeBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 在保存实体时，自动保存雪花ID值
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'doctrine_snowflake')]
readonly class SnowflakeListener implements EntityCheckerInterface
{
    public function __construct(
        #[Autowire(service: 'doctrine-snowflake.property-accessor')] private PropertyAccessor $propertyAccessor,
        private Snowflake $snowflake,
        private LoggerInterface $logger,
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
            $this->processSnowflakeProperty($entity, $property);
        }
    }

    private function processSnowflakeProperty(object $entity, \ReflectionProperty $property): void
    {
        if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
            return;
        }

        foreach ($property->getAttributes(SnowflakeColumn::class) as $attribute) {
            $snowflakeAttribute = $attribute->newInstance();
            $this->assignSnowflakeId($entity, $property, $snowflakeAttribute);
        }
    }

    private function assignSnowflakeId(object $entity, \ReflectionProperty $property, SnowflakeColumn $attribute): void
    {
        if ($this->hasExistingValue($entity, $property)) {
            return;
        }

        $idValue = $this->generateSnowflakeValue($attribute);
        if ('' === $idValue) {
            return;
        }

        $this->logger->debug("为{$property->getName()}分配雪花算法ID", [
            'id' => $idValue,
            'entity' => $entity,
        ]);

        $this->propertyAccessor->setValue($entity, $property->getName(), $idValue);
    }

    private function hasExistingValue(object $entity, \ReflectionProperty $property): bool
    {
        try {
            $value = $property->getValue($entity);

            return null !== $value && '' !== $value && 0 !== $value && false !== $value && [] !== $value;
        } catch (\Throwable) {
            return false;
        }
    }

    private function generateSnowflakeValue(SnowflakeColumn $attribute): string
    {
        $idValue = trim($this->snowflake->id());

        if ('' !== $attribute->prefix) {
            $idValue = "{$attribute->prefix}{$idValue}";
        }

        if ($attribute->length > 0) {
            $idValue = substr($idValue, 0, $attribute->length);
        }

        return $idValue;
    }

    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        // 更新时，我们不特地去处理，因为有可能我们是需要去数据库特地清空，故意不给值的
    }
}
