<?php

namespace Tourze\DoctrineSnowflakeBundle\Attribute;

/**
 * 标记字段自动生成雪花算法ID
 *
 * 我们不应该在 id 主键上使用这个注解，雪花id主键应使用 `#[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]`
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SnowflakeColumn
{
    public function __construct(
        public string $prefix = '', // 前缀
        public int $length = 0, // 长度限制
    ) {
    }
}
