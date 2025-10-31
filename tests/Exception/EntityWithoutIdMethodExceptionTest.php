<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineSnowflakeBundle\Exception\EntityWithoutIdMethodException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(EntityWithoutIdMethodException::class)]
final class EntityWithoutIdMethodExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructor(): void
    {
        $entity = new class {
            public string $name = 'test';
        };

        $exception = new EntityWithoutIdMethodException($entity);

        $this->assertStringContainsString(
            'must have getId() method',
            $exception->getMessage()
        );
        $this->assertStringContainsString(
            $entity::class,
            $exception->getMessage()
        );
    }
}
