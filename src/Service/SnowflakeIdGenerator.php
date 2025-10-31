<?php

declare(strict_types=1);

namespace Tourze\DoctrineSnowflakeBundle\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\DoctrineSnowflakeBundle\Exception\EntityWithoutIdMethodException;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 自动生成雪花ID
 * 理论上除非并发十分高，要不不可能有冲突的
 * 这个服务因为Doctrine需要直接引用，所以需要为public
 *
 * @see https://blog.csdn.net/helen920318/article/details/104952814
 */
#[AutoconfigureTag(name: 'doctrine.id_generator')]
#[Autoconfigure(public: true)]
class SnowflakeIdGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, ?object $entity): string
    {
        if (null === $entity) {
            return $this->generateNewId(0);
        }

        if (!method_exists($entity, 'getId')) {
            throw new EntityWithoutIdMethodException($entity);
        }

        $existingId = $this->getExistingId($entity);
        if (null !== $existingId && '' !== $existingId) {
            return $existingId;
        }

        $dataCenterId = static::generateDataCenterIdFromClassName(ClassUtils::getClass($entity));

        return $this->generateNewId($dataCenterId);
    }

    private function getExistingId(object $entity): ?string
    {
        try {
            $reflection = new \ReflectionClass($entity);
            if (!$reflection->hasMethod('getId')) {
                return null;
            }

            $method = $reflection->getMethod('getId');
            $result = $method->invoke($entity);

            return is_string($result) ? $result : null;
        } catch (\Throwable $exception) {
            if (str_contains($exception->getMessage(), 'must not be accessed before initialization')) {
                return null;
            }
            throw $exception;
        }
    }

    private function generateNewId(int $dataCenterId): string
    {
        $hostname = gethostname();
        $workerId = Snowflake::generateWorkerId(false !== $hostname ? $hostname : 'unknown');
        $generator = Snowflake::getGenerator($dataCenterId, $workerId);

        return $generator->id();
    }

    /**
     * 生成基于实体类名的数据中心ID
     *
     * @param string $className       实体类名
     * @param int    $maxDataCenterId 最大数据中心ID，通常根据Snowflake ID的实现选择
     *
     * @return int 生成的数据中心ID
     */
    public static function generateDataCenterIdFromClassName(string $className, int $maxDataCenterId = 31): int
    {
        // 将类名转换为一个哈希值
        $hash = crc32($className);

        // 将哈希值限制在数据中心ID的范围内
        return $hash % ($maxDataCenterId + 1);
    }
}
