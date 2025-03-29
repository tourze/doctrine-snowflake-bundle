<?php

namespace Tourze\DoctrineSnowflakeBundle\Attribute;

/**
 * 标记字段自动生成雪花算法ID
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
