<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Traits\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;

/**
 * 测试使用 SnowflakeKeyAware trait 的实体
 */
#[ORM\Entity]
#[ORM\Table(name: 'snowflake_key_aware_test_entity', options: ['comment' => '雪花ID测试实体'])]
class SnowflakeKeyAwareTestEntity implements \Stringable
{
    use SnowflakeKeyAware;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体名称'])]
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

    public function __toString(): string
    {
        return $this->name !== '' ? $this->name : 'SnowflakeKeyAwareTestEntity';
    }
}