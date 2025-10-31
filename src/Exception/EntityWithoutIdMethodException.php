<?php

declare(strict_types=1);

namespace Tourze\DoctrineSnowflakeBundle\Exception;

class EntityWithoutIdMethodException extends \InvalidArgumentException
{
    public function __construct(object $entity)
    {
        parent::__construct(sprintf('Entity %s must have getId() method', get_class($entity)));
    }
}
