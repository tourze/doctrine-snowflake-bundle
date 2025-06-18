<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Integration\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

/**
 * 用于测试的雪花ID实体
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_entity', options: ['comment' => '测试实体'])]
class TestEntity implements \Stringable
{
    /**
     * 实体ID
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '实体ID'])]
    #[SnowflakeColumn(prefix: 'TEST_', length: 32)]
    private string $id = '';

    /**
     * 实体名称
     */
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体名称'])]
    private string $name = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

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
        return $this->name ?: 'TestEntity';
    }
} 