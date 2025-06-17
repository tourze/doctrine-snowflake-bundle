<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;

/**
 * 测试使用 SnowflakeKeyAware trait 的实体
 */
#[ORM\Entity]
#[ORM\Table(name: 'snowflake_key_aware_test_entity')]
class SnowflakeKeyAwareTestEntity
{
    use SnowflakeKeyAware;

    /**
     * 实体名称
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}