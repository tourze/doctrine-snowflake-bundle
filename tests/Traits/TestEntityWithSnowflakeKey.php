<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Traits;

use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;

/**
 * 用于测试的实体类
 */
class TestEntityWithSnowflakeKey
{
    use SnowflakeKeyAware;
}
