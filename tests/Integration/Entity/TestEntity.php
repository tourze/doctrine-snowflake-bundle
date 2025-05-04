<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Integration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

/**
 * 用于测试的雪花ID实体
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_entity')]
class TestEntity
{
    /**
     * 实体ID
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    #[SnowflakeColumn(prefix: 'TEST_', length: 32)]
    private string $id = '';

    /**
     * 实体名称
     */
    #[ORM\Column(type: 'string', length: 255)]
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
} 